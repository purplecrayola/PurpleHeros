<?php

namespace App\Filament\Pages;

use App\Models\EmployeeOnboarding;
use App\Models\EmployeeReference;
use App\Support\InternalSignatureManager;
use App\Support\ReferenceCheckManager;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Throwable;

class OnboardingWorkspace extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $title = 'Onboarding Workspace';
    protected static ?string $slug = 'onboarding-workspace';
    protected static string $view = 'filament.pages.onboarding-workspace';

    public int $onboardingId = 0;
    public ?EmployeeOnboarding $onboarding = null;

    public bool $showReferenceResponsesModal = false;
    public array $referenceResponses = [];
    public bool $showRejectReferenceModal = false;
    public ?int $rejectReferenceId = null;
    public string $rejectReferenceReason = '';

    public bool $showAuditTrailModal = false;
    public array $auditTrailEntries = [];
    public string $auditEventFilter = '';
    public string $auditFromDate = '';
    public string $auditToDate = '';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->canManagePeopleOps();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(): void
    {
        $this->onboardingId = (int) request()->query('onboarding', 0);
        abort_unless($this->onboardingId > 0, 404);

        $this->loadOnboarding();
    }

    public function loadOnboarding(): void
    {
        $this->onboarding = EmployeeOnboarding::query()
            ->with([
                'user:user_id,name,email,department,position',
                'latestOfferSignatureRequest',
                'latestContractSignatureRequest',
            ])
            ->find($this->onboardingId);

        abort_unless($this->onboarding, 404);
    }

    public function markOfferSent(): void
    {
        if (! $this->onboarding) {
            return;
        }

        try {
            $request = InternalSignatureManager::createAndSend(
                $this->onboarding,
                'offer',
                $this->onboarding->offer_document_path,
                $this->onboarding->user?->name,
                $this->onboarding->user?->email
            );

            $this->onboarding->offer_status = 'sent';
            $this->onboarding->offer_sent_at = now()->toDateString();
            $this->onboarding->offer_sign_provider = 'purpleheros_internal';
            $this->onboarding->offer_signature_request_id = (string) $request->id;
            $this->onboarding->onboarding_status = $this->onboarding->onboarding_status === 'draft' ? 'in_progress' : $this->onboarding->onboarding_status;
            $this->onboarding->updated_by_user_id = auth()->user()?->user_id;
            $this->onboarding->save();

            $this->loadOnboarding();
            Notification::make()->title('Offer sent for signature')->success()->send();
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Could not send offer for signature')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function markOfferSigned(): void
    {
        if (! $this->onboarding) {
            return;
        }

        $this->onboarding->offer_status = 'signed';
        $this->onboarding->offer_signed_at = now()->toDateString();
        $this->onboarding->updated_by_user_id = auth()->user()?->user_id;
        $this->onboarding->save();

        $this->loadOnboarding();
        Notification::make()->title('Offer marked as signed')->success()->send();
    }

    public function markContractSent(): void
    {
        if (! $this->onboarding) {
            return;
        }

        try {
            $request = InternalSignatureManager::createAndSend(
                $this->onboarding,
                'contract',
                $this->onboarding->contract_document_path,
                $this->onboarding->user?->name,
                $this->onboarding->user?->email
            );

            $this->onboarding->contract_status = 'sent';
            $this->onboarding->contract_sent_at = now()->toDateString();
            $this->onboarding->contract_sign_provider = 'purpleheros_internal';
            $this->onboarding->contract_signature_request_id = (string) $request->id;
            $this->onboarding->onboarding_status = $this->onboarding->onboarding_status === 'draft' ? 'in_progress' : $this->onboarding->onboarding_status;
            $this->onboarding->updated_by_user_id = auth()->user()?->user_id;
            $this->onboarding->save();

            $this->loadOnboarding();
            Notification::make()->title('Contract sent for signature')->success()->send();
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Could not send contract for signature')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function markContractSigned(): void
    {
        if (! $this->onboarding) {
            return;
        }

        $this->onboarding->contract_status = 'signed';
        $this->onboarding->contract_signed_at = now()->toDateString();
        $this->onboarding->updated_by_user_id = auth()->user()?->user_id;
        $this->onboarding->save();

        $this->loadOnboarding();
        Notification::make()->title('Contract marked as signed')->success()->send();
    }

    public function sendReferenceRequests(): void
    {
        if (! $this->onboarding) {
            return;
        }

        try {
            $result = ReferenceCheckManager::sendForUser((string) $this->onboarding->user_id);

            $body = sprintf(
                'Sent: %d, Skipped (no email): %d, Failed: %d',
                (int) ($result['sent'] ?? 0),
                (int) ($result['skipped'] ?? 0),
                (int) ($result['failed'] ?? 0)
            );

            $this->syncReferenceStatusForUser((string) $this->onboarding->user_id);
            $this->loadOnboarding();

            Notification::make()->title('Reference requests processed')->body($body)->success()->send();
        } catch (Throwable $exception) {
            Notification::make()->title('Could not send referee requests')->body($exception->getMessage())->danger()->send();
        }
    }

    public function openReferenceResponses(): void
    {
        if (! $this->onboarding) {
            return;
        }

        $this->referenceResponses = EmployeeReference::query()
            ->where('user_id', $this->onboarding->user_id)
            ->orderByDesc('responded_at')
            ->orderByDesc('id')
            ->get([
                'id', 'referee_name', 'relationship', 'email', 'request_status',
                'requested_at', 'responded_at', 'response_rating', 'response_payload',
                'is_verified', 'verified_at',
            ])
            ->map(function (EmployeeReference $reference): array {
                $payload = is_array($reference->response_payload) ? $reference->response_payload : [];

                return [
                    'id' => (int) $reference->id,
                    'referee_name' => (string) ($reference->referee_name ?: 'Referee'),
                    'relationship' => (string) ($reference->relationship ?: '—'),
                    'email' => (string) ($reference->email ?: '—'),
                    'request_status' => (string) ($reference->request_status ?: 'not_sent'),
                    'requested_at' => $reference->requested_at?->format('Y-m-d H:i') ?: '—',
                    'responded_at' => $reference->responded_at?->format('Y-m-d H:i') ?: '—',
                    'response_rating' => $reference->response_rating !== null ? (string) $reference->response_rating : '—',
                    'comments' => trim((string) ($payload['comments'] ?? '')) ?: '—',
                    'is_verified' => (bool) $reference->is_verified,
                    'verified_at' => $reference->verified_at?->format('Y-m-d H:i') ?: '—',
                ];
            })
            ->values()
            ->all();

        $this->showReferenceResponsesModal = true;
    }

    public function closeReferenceResponses(): void
    {
        $this->showReferenceResponsesModal = false;
        $this->referenceResponses = [];
        $this->closeRejectReferenceModal();
    }

    public function verifyReference(int $referenceId): void
    {
        $reference = EmployeeReference::query()->find($referenceId);
        if (! $reference) {
            Notification::make()->title('Reference not found')->danger()->send();
            return;
        }

        $reference->is_verified = true;
        $reference->verified_by_user_id = auth()->user()?->user_id;
        $reference->verified_at = now();
        $reference->save();

        $this->syncReferenceStatusForUser((string) $reference->user_id);
        $this->openReferenceResponses();
        $this->loadOnboarding();

        Notification::make()->title('Reference marked as verified')->success()->send();
    }

    public function openRejectReferenceModal(int $referenceId): void
    {
        $this->rejectReferenceId = $referenceId;
        $this->rejectReferenceReason = '';
        $this->showRejectReferenceModal = true;
    }

    public function closeRejectReferenceModal(): void
    {
        $this->showRejectReferenceModal = false;
        $this->rejectReferenceId = null;
        $this->rejectReferenceReason = '';
    }

    public function rejectReference(): void
    {
        if (! $this->rejectReferenceId) {
            Notification::make()->title('Reference not selected')->danger()->send();
            return;
        }

        $reason = trim($this->rejectReferenceReason);
        if ($reason === '') {
            Notification::make()->title('Rejection note is required')->warning()->send();
            return;
        }

        $reference = EmployeeReference::query()->find($this->rejectReferenceId);
        if (! $reference) {
            Notification::make()->title('Reference not found')->danger()->send();
            return;
        }

        $reference->is_verified = false;
        $reference->verified_by_user_id = auth()->user()?->user_id;
        $reference->verified_at = now();
        $reference->verification_feedback = $reason;
        $reference->save();

        $this->syncReferenceStatusForUser((string) $reference->user_id);
        $this->closeRejectReferenceModal();
        $this->openReferenceResponses();
        $this->loadOnboarding();

        Notification::make()->title('Reference rejected with note')->warning()->send();
    }

    public function markOnboarded(): void
    {
        if (! $this->onboarding) {
            return;
        }

        $this->onboarding->onboarding_status = 'onboarded';
        $this->onboarding->onboarding_completed_at = now();
        $this->onboarding->updated_by_user_id = auth()->user()?->user_id;
        $this->onboarding->save();

        $this->loadOnboarding();
        Notification::make()->title('Employee marked as onboarded')->success()->send();
    }

    public function openAuditTrail(): void
    {
        if (! $this->onboarding) {
            return;
        }

        $entries = [];
        $requests = $this->onboarding->signatureRequests()
            ->with(['events' => fn ($q) => $q->orderByDesc('id')])
            ->orderByDesc('id')
            ->get();

        foreach ($requests as $request) {
            foreach ($request->events as $event) {
                $entries[] = [
                    'document_type' => (string) $request->document_type,
                    'request_id' => (int) $request->id,
                    'event_type' => (string) $event->event_type,
                    'at' => $event->created_at?->format('Y-m-d H:i:s') ?: '—',
                    'ip' => (string) ($event->ip_address ?: '—'),
                    'user_agent' => (string) ($event->user_agent ?: '—'),
                    'payload' => is_array($event->event_payload) && $event->event_payload !== []
                        ? json_encode($event->event_payload, JSON_UNESCAPED_SLASHES)
                        : '—',
                ];
            }
        }

        usort($entries, fn (array $a, array $b): int => strcmp((string) $b['at'], (string) $a['at']));
        $this->auditTrailEntries = $entries;
        $this->showAuditTrailModal = true;
    }

    public function closeAuditTrail(): void
    {
        $this->showAuditTrailModal = false;
        $this->auditTrailEntries = [];
        $this->auditEventFilter = '';
        $this->auditFromDate = '';
        $this->auditToDate = '';
    }

    public function clearAuditTrailFilters(): void
    {
        $this->auditEventFilter = '';
        $this->auditFromDate = '';
        $this->auditToDate = '';
    }

    public function getAuditEventOptions(): array
    {
        return [
            'request_created' => 'Request Created',
            'request_opened' => 'Request Opened',
            'signature_submitted' => 'Signature Submitted',
            'request_signed' => 'Request Signed',
            'request_downloaded' => 'Signed PDF Downloaded',
        ];
    }

    public function getFilteredAuditTrailEntries(): array
    {
        return collect($this->auditTrailEntries)
            ->filter(function (array $entry): bool {
                if ($this->auditEventFilter !== '' && ($entry['event_type'] ?? '') !== $this->auditEventFilter) {
                    return false;
                }
                if ($this->auditFromDate !== '' && ($entry['at'] ?? '') !== '—') {
                    if (substr((string) $entry['at'], 0, 10) < $this->auditFromDate) {
                        return false;
                    }
                }
                if ($this->auditToDate !== '' && ($entry['at'] ?? '') !== '—') {
                    if (substr((string) $entry['at'], 0, 10) > $this->auditToDate) {
                        return false;
                    }
                }

                return true;
            })
            ->values()
            ->all();
    }

    private function syncReferenceStatusForUser(string $userId): void
    {
        $onboarding = EmployeeOnboarding::query()->where('user_id', $userId)->first();
        if (! $onboarding) {
            return;
        }

        $total = EmployeeReference::query()->where('user_id', $userId)->count();
        $verified = EmployeeReference::query()->where('user_id', $userId)->where('is_verified', true)->count();
        $responded = EmployeeReference::query()->where('user_id', $userId)->where('request_status', 'responded')->count();
        $sent = EmployeeReference::query()->where('user_id', $userId)->where('request_status', 'sent')->count();

        $onboarding->references_total_count = $total;
        $onboarding->references_verified_count = $verified;

        if ($total > 0 && $verified >= $total) {
            $onboarding->reference_check_status = 'completed';
        } elseif ($responded > 0 || $sent > 0) {
            $onboarding->reference_check_status = 'in_progress';
        } else {
            $onboarding->reference_check_status = 'not_sent';
        }

        $onboarding->updated_by_user_id = auth()->user()?->user_id;
        $onboarding->save();
    }
}

