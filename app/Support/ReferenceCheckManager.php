<?php

namespace App\Support;

use App\Models\EmployeeOnboarding;
use App\Models\EmployeeReference;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class ReferenceCheckManager
{
    /**
     * @return array{sent:int, skipped:int, failed:int}
     */
    public static function sendForUser(string $userId): array
    {
        $refs = EmployeeReference::query()
            ->where('user_id', $userId)
            ->orderBy('id')
            ->get();

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        $employee = User::query()->where('user_id', $userId)->first();
        $employeeName = (string) ($employee?->name ?: $userId);

        foreach ($refs as $reference) {
            $email = trim((string) ($reference->email ?? ''));
            if ($email === '') {
                $skipped++;
                continue;
            }

            $token = trim((string) ($reference->request_token ?? ''));
            if ($token === '') {
                $token = Str::random(64);
            }

            $reference->request_token = $token;
            $reference->request_status = 'sent';
            $reference->requested_at = now();
            $reference->request_expires_at = now()->addDays(14);
            $reference->save();

            try {
                MailSettingsManager::apply();

                $link = route('reference/check/show', ['token' => $token]);
                $subject = 'Reference Request - ' . $employeeName;
                $body = "Hello {$reference->referee_name},\n\n"
                    . "You have been listed as a referee for {$employeeName}.\n"
                    . "Please complete this secure reference form:\n{$link}\n\n"
                    . "This link expires on {$reference->request_expires_at?->format('d M Y H:i')}.\n";

                Mail::raw($body, function ($message) use ($email, $subject): void {
                    $message->to($email)->subject($subject);
                });

                $sent++;
            } catch (Throwable $exception) {
                $reference->request_status = 'failed';
                $reference->save();
                $failed++;
            }
        }

        $onboarding = EmployeeOnboarding::query()->where('user_id', $userId)->first();
        if ($onboarding) {
            $hasSent = EmployeeReference::query()->where('user_id', $userId)->where('request_status', 'sent')->exists();
            $hasResponded = EmployeeReference::query()->where('user_id', $userId)->where('request_status', 'responded')->exists();

            if ($hasResponded || $hasSent) {
                $onboarding->reference_check_status = 'in_progress';
            }

            $onboarding->references_total_count = EmployeeReference::query()->where('user_id', $userId)->count();
            $onboarding->references_verified_count = EmployeeReference::query()->where('user_id', $userId)->where('is_verified', true)->count();
            $onboarding->updated_by_user_id = auth()->user()?->user_id;
            $onboarding->save();
        }

        return [
            'sent' => $sent,
            'skipped' => $skipped,
            'failed' => $failed,
        ];
    }

    public static function applyResponse(EmployeeReference $reference, array $payload): void
    {
        $reference->request_status = 'responded';
        $reference->responded_at = now();
        $reference->response_payload = $payload;
        $reference->response_rating = isset($payload['overall_rating']) ? (int) $payload['overall_rating'] : null;
        $reference->verification_feedback = trim((string) ($payload['comments'] ?? ''));
        $reference->save();

        $userId = (string) $reference->user_id;
        $onboarding = EmployeeOnboarding::query()->where('user_id', $userId)->first();

        if (! $onboarding) {
            return;
        }

        $onboarding->reference_check_status = 'in_progress';

        $total = EmployeeReference::query()->where('user_id', $userId)->count();
        $verified = EmployeeReference::query()->where('user_id', $userId)->where('is_verified', true)->count();
        $responded = EmployeeReference::query()->where('user_id', $userId)->where('request_status', 'responded')->count();

        $onboarding->references_total_count = $total;
        $onboarding->references_verified_count = $verified;

        if ($total > 0 && $verified >= $total) {
            $onboarding->reference_check_status = 'completed';
        } elseif ($responded > 0) {
            $onboarding->reference_check_status = 'in_progress';
        }

        $onboarding->save();
    }
}
