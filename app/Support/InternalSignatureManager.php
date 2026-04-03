<?php

namespace App\Support;

use App\Models\EmployeeOnboarding;
use App\Models\EmployeeSignatureEvent;
use App\Models\EmployeeSignatureRequest;
use App\Models\EmployeeSignatureSigner;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class InternalSignatureManager
{
    public static function createAndSend(
        EmployeeOnboarding $onboarding,
        string $documentType,
        ?string $documentPath,
        ?string $signerName,
        ?string $signerEmail
    ): EmployeeSignatureRequest {
        $type = in_array($documentType, ['offer', 'contract'], true) ? $documentType : 'other';

        $existingPending = EmployeeSignatureRequest::query()
            ->where('employee_onboarding_id', $onboarding->id)
            ->where('document_type', $type)
            ->where('status', 'pending')
            ->get();

        foreach ($existingPending as $pending) {
            $pending->status = 'cancelled';
            $pending->save();
            self::logEvent($pending, 'cancelled_replaced', [
                'reason' => 'new_signature_request_created',
            ]);
        }

        $actors = self::resolveActors($onboarding, $type, $signerName, $signerEmail);
        if ($actors === []) {
            throw new \RuntimeException('No signature actors configured. Add signer email(s) in onboarding signature fields.');
        }

        $request = EmployeeSignatureRequest::query()->create([
            'employee_onboarding_id' => $onboarding->id,
            'user_id' => $onboarding->user_id,
            'document_type' => $type,
            'document_path' => $documentPath,
            'status' => 'pending',
            'signer_name' => $signerName,
            'signer_email' => $signerEmail,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(14),
            'initiated_by_user_id' => auth()->user()?->user_id,
        ]);

        self::logEvent($request, 'created', [
            'document_type' => $type,
            'document_path' => $documentPath,
        ]);

        foreach ($actors as $actor) {
            EmployeeSignatureSigner::query()->create([
                'employee_signature_request_id' => $request->id,
                'role_label' => $actor['role_label'],
                'signer_name' => $actor['signer_name'],
                'signer_email' => $actor['signer_email'],
                'sign_order' => $actor['sign_order'],
                'signature_field_key' => $actor['signature_field_key'],
                'page_number' => $actor['page_number'],
                'position_x' => $actor['position_x'],
                'position_y' => $actor['position_y'],
                'field_width' => $actor['field_width'],
                'field_height' => $actor['field_height'],
                'status' => 'pending',
                'token' => Str::random(64),
            ]);
        }

        $nextSigner = self::getNextPendingSigner($request);
        if ($nextSigner) {
            self::sendSignerEmail($request, $nextSigner);
        }

        return $request->fresh(['signers', 'onboarding']);
    }

    public static function sign(
        EmployeeSignatureRequest $request,
        EmployeeSignatureSigner $signer,
        string $acknowledgement,
        string $ip,
        string $userAgent,
    ): void {
        $signedAt = now();

        $signerHashSource = implode('|', [
            $signer->id,
            $signer->token,
            $request->document_type,
            $request->document_path,
            trim($acknowledgement),
            $signedAt->toIso8601String(),
            $ip,
            $userAgent,
        ]);

        $signer->status = 'signed';
        $signer->signed_at = $signedAt;
        $signer->signed_acknowledgement = trim($acknowledgement);
        $signer->signed_hash = hash('sha256', $signerHashSource);
        $signer->completed_by_user_id = $request->user_id;
        $signer->save();

        self::logEvent($request, 'signer_signed', [
            'signer_id' => $signer->id,
            'role_label' => $signer->role_label,
            'signer_email' => $signer->signer_email,
            'signed_hash' => $signer->signed_hash,
            'field_key' => $signer->signature_field_key,
            'page_number' => $signer->page_number,
            'position_x' => $signer->position_x,
            'position_y' => $signer->position_y,
            'field_width' => $signer->field_width,
            'field_height' => $signer->field_height,
        ], $ip, $userAgent);

        $nextSigner = self::getNextPendingSigner($request->fresh('signers'));
        if ($nextSigner) {
            self::sendSignerEmail($request, $nextSigner);
            self::logEvent($request, 'signing_advanced', [
                'next_signer_id' => $nextSigner->id,
                'next_signer_email' => $nextSigner->signer_email,
                'next_role' => $nextSigner->role_label,
            ], $ip, $userAgent);

            return;
        }

        $request->refresh();
        $request->load('signers');

        $requestHashSource = implode('|', [
            $request->id,
            $request->token,
            $request->document_type,
            $request->document_path,
            $request->user_id,
            $request->signers->pluck('signed_hash')->implode('|'),
            $signedAt->toIso8601String(),
        ]);

        $request->status = 'signed';
        $request->signed_at = $signedAt;
        $request->signed_acknowledgement = 'Completed by all configured actors';
        $request->signed_hash = hash('sha256', $requestHashSource);
        $request->completed_by_user_id = $request->user_id;
        $request->signed_document_path = self::buildSignedArchivePdf($request, $ip, $userAgent);
        $request->save();

        self::logEvent($request, 'signed', [
            'signed_hash' => $request->signed_hash,
            'signed_at' => $request->signed_at?->toIso8601String(),
            'signed_document_path' => $request->signed_document_path,
            'actors_total' => $request->signers->count(),
        ], $ip, $userAgent);

        self::syncOnboardingStatusFromSignature($request);
    }

    public static function logEvent(
        EmployeeSignatureRequest $request,
        string $eventType,
        array $payload = [],
        ?string $ip = null,
        ?string $userAgent = null
    ): void {
        EmployeeSignatureEvent::query()->create([
            'employee_signature_request_id' => $request->id,
            'event_type' => $eventType,
            'event_payload' => $payload,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);
    }

    public static function getNextPendingSigner(EmployeeSignatureRequest $request): ?EmployeeSignatureSigner
    {
        return EmployeeSignatureSigner::query()
            ->where('employee_signature_request_id', $request->id)
            ->where('status', 'pending')
            ->orderBy('sign_order')
            ->orderBy('id')
            ->first();
    }

    /**
     * @return array<int, array{role_label:string, signer_name:string, signer_email:string, sign_order:int, signature_field_key:string, page_number:int, position_x:?float, position_y:?float, field_width:?float, field_height:?float}>
     */
    private static function resolveActors(
        EmployeeOnboarding $onboarding,
        string $type,
        ?string $fallbackSignerName,
        ?string $fallbackSignerEmail,
    ): array {
        $template = $type === 'offer' ? ($onboarding->offer_signers_json ?? []) : ($onboarding->contract_signers_json ?? []);

        $actors = [];
        if (is_array($template)) {
            foreach ($template as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $email = trim((string) ($row['signer_email'] ?? ''));
                if ($email === '') {
                    continue;
                }

                $actors[] = [
                    'role_label' => trim((string) ($row['role_label'] ?? 'Signer')),
                    'signer_name' => trim((string) ($row['signer_name'] ?? '')),
                    'signer_email' => $email,
                    'sign_order' => max(1, (int) ($row['sign_order'] ?? 1)),
                    'signature_field_key' => trim((string) ($row['signature_field_key'] ?? 'SIGNATURE_1')),
                    'page_number' => max(1, (int) ($row['page_number'] ?? 1)),
                    'position_x' => isset($row['position_x']) && $row['position_x'] !== '' ? (float) $row['position_x'] : null,
                    'position_y' => isset($row['position_y']) && $row['position_y'] !== '' ? (float) $row['position_y'] : null,
                    'field_width' => isset($row['field_width']) && $row['field_width'] !== '' ? (float) $row['field_width'] : null,
                    'field_height' => isset($row['field_height']) && $row['field_height'] !== '' ? (float) $row['field_height'] : null,
                ];
            }
        }

        if ($actors === []) {
            $email = trim((string) ($fallbackSignerEmail ?: ''));
            if ($email === '') {
                $email = trim((string) (User::query()->where('user_id', $onboarding->user_id)->value('email') ?: ''));
            }

            $name = trim((string) ($fallbackSignerName ?: ''));
            if ($name === '') {
                $name = trim((string) (User::query()->where('user_id', $onboarding->user_id)->value('name') ?: $onboarding->user_id));
            }

            if ($email !== '') {
                $actors[] = [
                    'role_label' => 'Employee',
                    'signer_name' => $name,
                    'signer_email' => $email,
                    'sign_order' => 1,
                    'signature_field_key' => 'EMPLOYEE_SIGN',
                    'page_number' => 1,
                    'position_x' => null,
                    'position_y' => null,
                    'field_width' => null,
                    'field_height' => null,
                ];
            }
        }

        usort($actors, fn (array $a, array $b): int => ((int) $a['sign_order']) <=> ((int) $b['sign_order']));

        return array_values($actors);
    }

    private static function sendSignerEmail(EmployeeSignatureRequest $request, EmployeeSignatureSigner $signer): void
    {
        $recipient = trim((string) ($signer->signer_email ?? ''));
        if ($recipient === '') {
            return;
        }

        try {
            MailSettingsManager::apply();

            $link = route('signature/request/show', ['token' => $signer->token]);
            $subject = 'PurpleHeros Signature Request - ' . strtoupper($request->document_type) . ' (' . ($signer->role_label ?: 'Signer') . ')';
            $body = "Hello {$signer->signer_name},\n\n"
                . "You have a pending {$request->document_type} signature step in PurpleHeros.\n"
                . "Role: " . ($signer->role_label ?: 'Signer') . "\n"
                . "Field Key: " . ($signer->signature_field_key ?: 'SIGNATURE') . "\n"
                . "Page: " . ((int) ($signer->page_number ?: 1)) . "\n"
                . "Position: X=" . ($signer->position_x ?? '-') . ", Y=" . ($signer->position_y ?? '-') . ", W=" . ($signer->field_width ?? '-') . ", H=" . ($signer->field_height ?? '-') . "\n\n"
                . "Sign here: {$link}\n\n"
                . "This link expires on {$request->expires_at?->format('d M Y H:i')}.\n";

            Mail::raw($body, function ($message) use ($recipient, $subject): void {
                $message->to($recipient)->subject($subject);
            });

            self::logEvent($request, 'email_sent', [
                'recipient' => $recipient,
                'signer_id' => $signer->id,
                'role_label' => $signer->role_label,
            ]);
        } catch (Throwable $exception) {
            self::logEvent($request, 'email_failed', [
                'recipient' => $recipient,
                'signer_id' => $signer->id,
                'role_label' => $signer->role_label,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private static function syncOnboardingStatusFromSignature(EmployeeSignatureRequest $request): void
    {
        $onboarding = $request->onboarding;
        if (! $onboarding) {
            return;
        }

        if ($request->document_type === 'offer') {
            $onboarding->offer_status = 'signed';
            $onboarding->offer_signed_at = now()->toDateString();
            $onboarding->offer_signature_request_id = (string) $request->id;
            $onboarding->offer_sign_provider = 'purpleheros_internal';
        } elseif ($request->document_type === 'contract') {
            $onboarding->contract_status = 'signed';
            $onboarding->contract_signed_at = now()->toDateString();
            $onboarding->contract_signature_request_id = (string) $request->id;
            $onboarding->contract_sign_provider = 'purpleheros_internal';
        }

        if ($onboarding->onboarding_status === 'draft') {
            $onboarding->onboarding_status = 'in_progress';
        }

        $onboarding->updated_by_user_id = $request->user_id;
        $onboarding->save();
    }

    private static function buildSignedArchivePdf(
        EmployeeSignatureRequest $request,
        string $ip,
        string $userAgent,
    ): ?string {
        $request->loadMissing('onboarding', 'signers');

        $employee = User::query()->where('user_id', $request->user_id)->first();
        $employeeName = (string) ($employee?->name ?: $request->signer_name ?: $request->user_id);

        $fileName = sprintf(
            'signed-%s-%s-%s.pdf',
            Str::slug($request->document_type),
            Str::slug($employeeName),
            now()->format('YmdHis')
        );

        $relativeDirectory = 'signature-archive/' . now()->format('Y/m');
        $relativePath = $relativeDirectory . '/' . $fileName;
        $absoluteDirectory = storage_path('app/' . $relativeDirectory);
        $absolutePath = storage_path('app/' . $relativePath);

        if (! is_dir($absoluteDirectory)) {
            mkdir($absoluteDirectory, 0750, true);
        }

        $pdfBinary = Pdf::loadView('signatures.signed-archive-pdf', [
            'request' => $request,
            'onboarding' => $request->onboarding,
            'employee' => $employee,
            'employee_name' => $employeeName,
            'signed_at' => $request->signed_at ?: now(),
            'signed_hash' => (string) $request->signed_hash,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'signers' => $request->signers,
        ])->setPaper('a4')->output();

        file_put_contents($absolutePath, $pdfBinary);
        @chmod($absolutePath, 0440);

        return $relativePath;
    }
}
