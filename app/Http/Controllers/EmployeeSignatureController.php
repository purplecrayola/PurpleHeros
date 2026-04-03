<?php

namespace App\Http\Controllers;

use App\Models\EmployeeSignatureRequest;
use App\Models\EmployeeSignatureSigner;
use App\Support\InternalSignatureManager;
use App\Support\MediaStorageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmployeeSignatureController extends Controller
{
    public function show(string $token): View
    {
        $activeSigner = EmployeeSignatureSigner::query()
            ->with(['signatureRequest.onboarding.user:user_id,name,email', 'signatureRequest.signers'])
            ->where('token', $token)
            ->first();

        $signatureRequest = $activeSigner?->signatureRequest;

        if (! $signatureRequest) {
            $signatureRequest = EmployeeSignatureRequest::query()
                ->with(['onboarding.user:user_id,name,email', 'signers'])
                ->where('token', $token)
                ->first();

            if (! $signatureRequest) {
                abort(404);
            }
        }

        if ($signatureRequest->status === 'pending' && $signatureRequest->expires_at && $signatureRequest->expires_at->isPast()) {
            $signatureRequest->status = 'expired';
            $signatureRequest->save();
            InternalSignatureManager::logEvent($signatureRequest, 'expired_on_view');
        }

        InternalSignatureManager::logEvent($signatureRequest, 'opened', [
            'status' => $signatureRequest->status,
            'signer_id' => $activeSigner?->id,
            'signer_email' => $activeSigner?->signer_email,
        ], request()->ip(), (string) request()->userAgent());

        $documentUrl = null;
        if (filled($signatureRequest->document_path)) {
            $documentUrl = MediaStorageManager::publicUrl((string) $signatureRequest->document_path, '');
        }

        $nextPendingSigner = InternalSignatureManager::getNextPendingSigner($signatureRequest);
        $canSign = $activeSigner
            && $activeSigner->status === 'pending'
            && $signatureRequest->status === 'pending'
            && (! $signatureRequest->expires_at || ! $signatureRequest->expires_at->isPast())
            && $nextPendingSigner
            && (int) $nextPendingSigner->id === (int) $activeSigner->id;

        return view('signatures.request', [
            'signatureRequest' => $signatureRequest,
            'activeSigner' => $activeSigner,
            'canSign' => $canSign,
            'documentUrl' => $documentUrl,
            'signedArchiveDownloadUrl' => $signatureRequest->signed_document_path
                ? route('signature/request/download-signed', ['token' => $signatureRequest->token])
                : null,
        ]);
    }

    public function submit(Request $httpRequest, string $token): RedirectResponse
    {
        $signer = EmployeeSignatureSigner::query()
            ->with(['signatureRequest.onboarding', 'signatureRequest.signers'])
            ->where('token', $token)
            ->first();

        if (! $signer) {
            abort(404);
        }

        $signatureRequest = $signer->signatureRequest;
        if (! $signatureRequest) {
            abort(404);
        }

        $httpRequest->validate([
            'typed_signature_name' => ['required', 'string', 'max:120'],
            'consent' => ['accepted'],
        ], [
            'consent.accepted' => 'You must accept the declaration before signing.',
        ]);

        if ($signatureRequest->status !== 'pending') {
            return redirect()
                ->route('signature/request/show', ['token' => $signer->token])
                ->with('error', 'This signature request is not pending anymore.');
        }

        if ($signer->status !== 'pending') {
            return redirect()
                ->route('signature/request/show', ['token' => $signer->token])
                ->with('error', 'Your signature stage has already been completed.');
        }

        if ($signatureRequest->expires_at && $signatureRequest->expires_at->isPast()) {
            $signatureRequest->status = 'expired';
            $signatureRequest->save();
            InternalSignatureManager::logEvent($signatureRequest, 'expired_on_submit', [
                'signer_id' => $signer->id,
                'signer_email' => $signer->signer_email,
            ], $httpRequest->ip(), (string) $httpRequest->userAgent());

            return redirect()
                ->route('signature/request/show', ['token' => $signer->token])
                ->with('error', 'This signature request has expired.');
        }

        $nextPendingSigner = InternalSignatureManager::getNextPendingSigner($signatureRequest);
        if (! $nextPendingSigner || (int) $nextPendingSigner->id !== (int) $signer->id) {
            return redirect()
                ->route('signature/request/show', ['token' => $signer->token])
                ->with('error', 'This stage is locked until previous signer stages are completed.');
        }

        $typedName = trim((string) $httpRequest->input('typed_signature_name'));

        $acknowledgement = 'I, ' . $typedName
            . ' (' . ($signer->role_label ?: 'Signer') . '), confirm that I reviewed and signed this '
            . strtoupper($signatureRequest->document_type)
            . ' document for field ' . ($signer->signature_field_key ?: 'SIGNATURE')
            . ' on page ' . ((int) ($signer->page_number ?: 1))
            . ' [X=' . ($signer->position_x ?? '-') . ',Y=' . ($signer->position_y ?? '-') . ',W=' . ($signer->field_width ?? '-') . ',H=' . ($signer->field_height ?? '-') . ']'
            . ' at ' . now()->format('d M Y H:i');

        InternalSignatureManager::sign(
            $signatureRequest,
            $signer,
            $acknowledgement,
            (string) ($httpRequest->ip() ?: ''),
            (string) ($httpRequest->userAgent() ?: '')
        );

        return redirect()
            ->route('signature/request/show', ['token' => $signer->token])
            ->with('success', 'Signature stage completed successfully.');
    }

    public function downloadSigned(string $token): BinaryFileResponse
    {
        $signatureRequest = EmployeeSignatureRequest::query()
            ->where('token', $token)
            ->first();

        if (! $signatureRequest) {
            abort(404);
        }

        abort_unless(
            $signatureRequest->status === 'signed' && filled($signatureRequest->signed_document_path),
            404
        );

        $filePath = storage_path('app/' . ltrim((string) $signatureRequest->signed_document_path, '/'));
        abort_unless(is_file($filePath), 404);

        $downloadName = sprintf(
            'Signed-%s-%s.pdf',
            ucfirst((string) $signatureRequest->document_type),
            preg_replace('/[^A-Za-z0-9\-_]/', '_', (string) ($signatureRequest->user_id ?: 'Employee'))
        );

        return response()->download($filePath, $downloadName);
    }
}
