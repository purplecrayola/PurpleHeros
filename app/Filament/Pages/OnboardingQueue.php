<?php

namespace App\Filament\Pages;

use App\Models\EmployeeOnboarding;
use App\Models\EmployeeReference;
use App\Support\InternalSignatureManager;
use App\Support\ReferenceCheckManager;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Throwable;

class OnboardingQueue extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationGroup = 'People';
    protected static ?int $navigationSort = 24;
    protected static ?string $title = 'Onboarding Queue';
    protected static ?string $slug = 'onboarding-queue';

    protected static string $view = 'filament.pages.onboarding-queue';

    public string $search = '';
    public string $statusFilter = '';
    public string $signatureFilter = '';
    public bool $showAuditTrailModal = false;
    public string $auditTrailEmployeeName = '';
    public array $auditTrailEntries = [];
    public string $auditEventFilter = '';
    public string $auditFromDate = '';
    public string $auditToDate = '';
    public bool $showReferenceResponsesModal = false;
    public string $referenceResponsesEmployeeName = '';
    public array $referenceResponses = [];
    public ?int $referenceResponsesOnboardingId = null;
    public bool $showRejectReferenceModal = false;
    public ?int $rejectReferenceId = null;
    public string $rejectReferenceReason = '';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->canManagePeopleOps();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getSummary(): array
    {
        $base = EmployeeOnboarding::query();

        return [
            'active' => (clone $base)->whereIn('onboarding_status', ['draft', 'in_progress', 'ready_to_join'])->count(),
            'offer_pending_signature' => (clone $base)->where('offer_status', 'sent')->count(),
            'contract_pending_signature' => (clone $base)->where('contract_status', 'sent')->count(),
            'reference_pending' => (clone $base)->whereIn('reference_check_status', ['pending', 'not_sent', 'in_progress'])->count(),
            'onboarded_this_month' => (clone $base)
                ->where('onboarding_status', 'onboarded')
                ->whereNotNull('onboarding_completed_at')
                ->whereBetween('onboarding_completed_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
        ];
    }

    public function getRows(): Collection
    {
        return $this->baseQuery()
            ->with([
                'user:user_id,name,department,position,email',
                'latestOfferSignatureRequest',
                'latestContractSignatureRequest',
            ])
            ->orderByRaw("CASE onboarding_status
                WHEN 'in_progress' THEN 1
                WHEN 'ready_to_join' THEN 2
                WHEN 'draft' THEN 3
                WHEN 'onboarded' THEN 4
                ELSE 5 END")
            ->orderBy('planned_start_date')
            ->limit(250)
            ->get();
    }

    public function getStatusOptions(): array
    {
        return [
            '' => 'All statuses',
            'draft' => 'Draft',
            'in_progress' => 'In Progress',
            'ready_to_join' => 'Ready to Join',
            'onboarded' => 'Onboarded',
            'cancelled' => 'Cancelled',
        ];
    }

    public function getSignatureOptions(): array
    {
        return [
            '' => 'All signing states',
            'offer_sent' => 'Offer Sent (Awaiting Signature)',
            'contract_sent' => 'Contract Sent (Awaiting Signature)',
            'both_signed' => 'Offer + Contract Signed',
        ];
    }

    public function markOfferSent(int $id): void
    {
        $row = EmployeeOnboarding::query()
            ->with('user:user_id,name,email')
            ->find($id);
        if (! $row) {
            return;
        }

        try {
            $request = InternalSignatureManager::createAndSend(
                $row,
                'offer',
                $row->offer_document_path,
                $row->user?->name,
                $row->user?->email
            );

            $row->offer_status = 'sent';
            $row->offer_sent_at = now()->toDateString();
            $row->offer_sign_provider = 'purpleheros_internal';
            $row->offer_signature_request_id = (string) $request->id;
            $row->onboarding_status = $row->onboarding_status === 'draft' ? 'in_progress' : $row->onboarding_status;
            $row->updated_by_user_id = auth()->user()?->user_id;
            $row->save();

            Notification::make()->title('Offer sent for signature')->success()->send();
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Could not send offer for signature')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function markOfferSigned(int $id): void
    {
        $row = EmployeeOnboarding::query()->find($id);
        if (! $row) {
            return;
        }

        $row->offer_status = 'signed';
        $row->offer_signed_at = now()->toDateString();
        $row->updated_by_user_id = auth()->user()?->user_id;
        $row->save();

        Notification::make()->title('Offer marked as signed')->success()->send();
    }

    public function markContractSent(int $id): void
    {
        $row = EmployeeOnboarding::query()
            ->with('user:user_id,name,email')
            ->find($id);
        if (! $row) {
            return;
        }

        try {
            $request = InternalSignatureManager::createAndSend(
                $row,
                'contract',
                $row->contract_document_path,
                $row->user?->name,
                $row->user?->email
            );

            $row->contract_status = 'sent';
            $row->contract_sent_at = now()->toDateString();
            $row->contract_sign_provider = 'purpleheros_internal';
            $row->contract_signature_request_id = (string) $request->id;
            $row->onboarding_status = $row->onboarding_status === 'draft' ? 'in_progress' : $row->onboarding_status;
            $row->updated_by_user_id = auth()->user()?->user_id;
            $row->save();

            Notification::make()->title('Contract sent for signature')->success()->send();
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Could not send contract for signature')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function markContractSigned(int $id): void
    {
        $row = EmployeeOnboarding::query()->find($id);
        if (! $row) {
            return;
        }

        $row->contract_status = 'signed';
        $row->contract_signed_at = now()->toDateString();
        $row->updated_by_user_id = auth()->user()?->user_id;
        $row->save();

        Notification::make()->title('Contract marked as signed')->success()->send();
    }

    public function markOnboarded(int $id): void
    {
        $row = EmployeeOnboarding::query()->find($id);
        if (! $row) {
            return;
        }

        $row->onboarding_status = 'onboarded';
        $row->onboarding_completed_at = now();
        $row->updated_by_user_id = auth()->user()?->user_id;
        $row->save();

        Notification::make()->title('Employee marked as onboarded')->success()->send();
    }

    public function sendReferenceRequests(int $id): void
    {
        $row = EmployeeOnboarding::query()
            ->with('user:user_id,name')
            ->find($id);
        if (! $row) {
            Notification::make()->title('Onboarding record not found')->danger()->send();

            return;
        }

        try {
            $result = ReferenceCheckManager::sendForUser((string) $row->user_id);

            $body = sprintf(
                'Sent: %d, Skipped (no email): %d, Failed: %d',
                (int) ($result['sent'] ?? 0),
                (int) ($result['skipped'] ?? 0),
                (int) ($result['failed'] ?? 0)
            );

            $this->syncReferenceStatusForUser((string) $row->user_id);

            Notification::make()
                ->title('Reference requests processed')
                ->body($body)
                ->success()
                ->send();
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Could not send referee requests')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function openReferenceResponses(int $id): void
    {
        $row = EmployeeOnboarding::query()
            ->with('user:user_id,name,email')
            ->find($id);
        if (! $row) {
            Notification::make()->title('Onboarding record not found')->danger()->send();

            return;
        }

        $this->referenceResponsesOnboardingId = $row->id;
        $this->referenceResponsesEmployeeName = (string) ($row->user?->name ?: $row->user_id);
        $this->referenceResponses = EmployeeReference::query()
            ->where('user_id', $row->user_id)
            ->orderByDesc('responded_at')
            ->orderByDesc('id')
            ->get([
                'id',
                'referee_name',
                'relationship',
                'email',
                'request_status',
                'requested_at',
                'responded_at',
                'response_rating',
                'response_payload',
                'is_verified',
                'verified_at',
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
        $this->referenceResponsesEmployeeName = '';
        $this->referenceResponses = [];
        $this->referenceResponsesOnboardingId = null;
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
        $this->refreshReferenceResponsesModal();

        Notification::make()->title('Reference marked as verified')->success()->send();
    }

    public function openRejectReferenceModal(int $referenceId): void
    {
        $reference = EmployeeReference::query()->find($referenceId);
        if (! $reference) {
            Notification::make()->title('Reference not found')->danger()->send();

            return;
        }

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
        $referenceId = $this->rejectReferenceId;
        if (! $referenceId) {
            Notification::make()->title('Reference not selected')->danger()->send();

            return;
        }

        $reason = trim($this->rejectReferenceReason);
        if ($reason === '') {
            Notification::make()
                ->title('Rejection note is required')
                ->body('Please provide a short reason before rejecting this reference.')
                ->warning()
                ->send();

            return;
        }

        $reference = EmployeeReference::query()->find($referenceId);
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
        $this->refreshReferenceResponsesModal();
        $this->closeRejectReferenceModal();

        Notification::make()->title('Reference rejected with note')->warning()->send();
    }

    public function openAuditTrail(int $id): void
    {
        $row = EmployeeOnboarding::query()
            ->with([
                'user:user_id,name,email',
                'signatureRequests' => fn ($query) => $query
                    ->select('id', 'employee_onboarding_id', 'document_type', 'status', 'token', 'signed_hash', 'signed_at', 'created_at')
                    ->orderByDesc('id')
                    ->with(['events' => fn ($eventsQuery) => $eventsQuery
                        ->select('id', 'employee_signature_request_id', 'event_type', 'event_payload', 'ip_address', 'user_agent', 'created_at')
                        ->orderByDesc('id'),
                    ]),
            ])
            ->find($id);

        if (! $row) {
            Notification::make()->title('Onboarding record not found')->danger()->send();

            return;
        }

        $this->auditTrailEmployeeName = (string) ($row->user?->name ?: $row->user_id);

        $entries = [];
        foreach ($row->signatureRequests as $request) {
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
        $this->auditEventFilter = '';
        $this->auditFromDate = '';
        $this->auditToDate = '';
        $this->showAuditTrailModal = true;
    }

    public function closeAuditTrail(): void
    {
        $this->showAuditTrailModal = false;
        $this->auditTrailEmployeeName = '';
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
        $options = collect($this->auditTrailEntries)
            ->pluck('event_type')
            ->map(fn ($value) => (string) $value)
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return array_reduce($options, function (array $carry, string $eventType): array {
            $carry[$eventType] = str($eventType)->replace('_', ' ')->title()->toString();

            return $carry;
        }, []);
    }

    public function getFilteredAuditTrailEntries(): array
    {
        return collect($this->auditTrailEntries)
            ->filter(function (array $entry): bool {
                if ($this->auditEventFilter !== '' && (string) ($entry['event_type'] ?? '') !== $this->auditEventFilter) {
                    return false;
                }

                $entryDate = substr((string) ($entry['at'] ?? ''), 0, 10);

                if ($this->auditFromDate !== '' && ($entryDate === '' || $entryDate < $this->auditFromDate)) {
                    return false;
                }

                if ($this->auditToDate !== '' && ($entryDate === '' || $entryDate > $this->auditToDate)) {
                    return false;
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

    private function refreshReferenceResponsesModal(): void
    {
        if (! $this->showReferenceResponsesModal || ! $this->referenceResponsesOnboardingId) {
            return;
        }

        $this->openReferenceResponses((int) $this->referenceResponsesOnboardingId);
    }

    private function baseQuery(): Builder
    {
        return EmployeeOnboarding::query()
            ->when($this->search !== '', function (Builder $query): void {
                $term = $this->search;
                $query->where(function (Builder $inner) use ($term): void {
                    $inner->where('user_id', 'like', "%{$term}%")
                        ->orWhereHas('user', function (Builder $userQuery) use ($term): void {
                            $userQuery->where('name', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%")
                                ->orWhere('department', 'like', "%{$term}%");
                        });
                });
            })
            ->when($this->statusFilter !== '', fn (Builder $query): Builder => $query->where('onboarding_status', $this->statusFilter))
            ->when($this->signatureFilter === 'offer_sent', fn (Builder $query): Builder => $query->where('offer_status', 'sent'))
            ->when($this->signatureFilter === 'contract_sent', fn (Builder $query): Builder => $query->where('contract_status', 'sent'))
            ->when($this->signatureFilter === 'both_signed', fn (Builder $query): Builder => $query->where('offer_status', 'signed')->where('contract_status', 'signed'));
    }
}
