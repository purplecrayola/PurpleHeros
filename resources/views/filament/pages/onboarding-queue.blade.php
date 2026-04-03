<x-filament-panels::page>
    @php($summary = $this->getSummary())

    <x-filament::section heading="Onboarding Queue">
        <div class="grid gap-3" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));">
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Active</div>
                <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($summary['active']) }}</div>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <div class="text-xs font-medium uppercase tracking-wide text-amber-700">Offer Pending</div>
                <div class="mt-1 text-2xl font-bold text-amber-900">{{ number_format($summary['offer_pending_signature']) }}</div>
            </div>
            <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4">
                <div class="text-xs font-medium uppercase tracking-wide text-indigo-700">Contract Pending</div>
                <div class="mt-1 text-2xl font-bold text-indigo-900">{{ number_format($summary['contract_pending_signature']) }}</div>
            </div>
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                <div class="text-xs font-medium uppercase tracking-wide text-rose-700">Reference Pending</div>
                <div class="mt-1 text-2xl font-bold text-rose-900">{{ number_format($summary['reference_pending']) }}</div>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <div class="text-xs font-medium uppercase tracking-wide text-emerald-700">Onboarded This Month</div>
                <div class="mt-1 text-2xl font-bold text-emerald-900">{{ number_format($summary['onboarded_this_month']) }}</div>
            </div>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" class="w-full rounded-lg border-slate-300 text-sm" placeholder="Employee, ID, email" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Onboarding Status</label>
                <select wire:model.live="statusFilter" class="w-full rounded-lg border-slate-300 text-sm">
                    @foreach ($this->getStatusOptions() as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Signature Stage</label>
                <select wire:model.live="signatureFilter" class="w-full rounded-lg border-slate-300 text-sm">
                    @foreach ($this->getSignatureOptions() as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-filament::section>

    @php($rows = $this->getRows())

    <x-filament::section heading="Candidates">
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-slate-800">Employee</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-800">Workflow</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-800">Offer</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-800">Contract</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-800">References</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-800">Signed Archive</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-800">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($rows as $row)
                        @php($offerRequest = $row->latestOfferSignatureRequest)
                        @php($contractRequest = $row->latestContractSignatureRequest)
                        <tr>
                            <td class="px-3 py-2 align-top">
                                <div class="font-medium text-slate-900">{{ $row->user?->name ?: $row->user_id }}</div>
                                <div class="text-xs text-slate-500">{{ $row->user_id }}</div>
                                <div class="text-xs text-slate-500">{{ $row->user?->email ?: 'No email' }}</div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">{{ str($row->onboarding_status)->replace('_', ' ')->title() }}</span>
                                <div class="mt-1 text-xs text-slate-500">Start: {{ optional($row->planned_start_date)->format('M j, Y') ?: 'Not set' }}</div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="text-sm text-slate-800">{{ str($row->offer_status)->replace('_', ' ')->title() }}</div>
                                <div class="text-xs text-slate-500">{{ $row->offer_sign_provider ? str($row->offer_sign_provider)->replace('_', ' ')->title() : 'No provider' }}</div>
                                <div class="text-xs text-slate-500">Sent: {{ optional($row->offer_sent_at)->format('M j, Y') ?: '—' }}</div>
                                <div class="text-xs text-slate-500">Signed: {{ optional($row->offer_signed_at)->format('M j, Y') ?: '—' }}</div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="text-sm text-slate-800">{{ str($row->contract_status)->replace('_', ' ')->title() }}</div>
                                <div class="text-xs text-slate-500">{{ $row->contract_sign_provider ? str($row->contract_sign_provider)->replace('_', ' ')->title() : 'No provider' }}</div>
                                <div class="text-xs text-slate-500">Sent: {{ optional($row->contract_sent_at)->format('M j, Y') ?: '—' }}</div>
                                <div class="text-xs text-slate-500">Signed: {{ optional($row->contract_signed_at)->format('M j, Y') ?: '—' }}</div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="text-sm text-slate-800">{{ str($row->reference_check_status)->replace('_', ' ')->title() }}</div>
                                <div class="text-xs text-slate-500">{{ (int) $row->references_verified_count }} / {{ (int) $row->references_total_count }} verified</div>
                                <div class="text-xs text-slate-500">Background: {{ str($row->background_check_status)->replace('_', ' ')->title() }}</div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="mb-2 rounded-lg border border-slate-200 p-2">
                                    <div class="text-xs font-semibold text-slate-700">Offer</div>
                                    <div class="text-xs text-slate-500">
                                        {{ $offerRequest?->status ? str($offerRequest->status)->replace('_', ' ')->title() : 'No request' }}
                                    </div>
                                    @if ($offerRequest?->signed_at)
                                        <div class="text-xs text-slate-500">Signed: {{ $offerRequest->signed_at->format('M j, Y H:i') }}</div>
                                    @endif
                                    @if ($offerRequest?->signed_hash)
                                        <div class="text-xs text-slate-500">Hash: {{ \Illuminate\Support\Str::limit($offerRequest->signed_hash, 18, '...') }}</div>
                                    @endif
                                    @if ($offerRequest?->signed_document_path)
                                        <a class="mt-1 inline-flex text-xs font-medium text-primary-600 hover:underline"
                                           href="{{ route('signature/request/download-signed', ['token' => $offerRequest->token]) }}"
                                           target="_blank"
                                           rel="noopener">
                                            Download PDF
                                        </a>
                                    @endif
                                </div>

                                <div class="rounded-lg border border-slate-200 p-2">
                                    <div class="text-xs font-semibold text-slate-700">Contract</div>
                                    <div class="text-xs text-slate-500">
                                        {{ $contractRequest?->status ? str($contractRequest->status)->replace('_', ' ')->title() : 'No request' }}
                                    </div>
                                    @if ($contractRequest?->signed_at)
                                        <div class="text-xs text-slate-500">Signed: {{ $contractRequest->signed_at->format('M j, Y H:i') }}</div>
                                    @endif
                                    @if ($contractRequest?->signed_hash)
                                        <div class="text-xs text-slate-500">Hash: {{ \Illuminate\Support\Str::limit($contractRequest->signed_hash, 18, '...') }}</div>
                                    @endif
                                    @if ($contractRequest?->signed_document_path)
                                        <a class="mt-1 inline-flex text-xs font-medium text-primary-600 hover:underline"
                                           href="{{ route('signature/request/download-signed', ['token' => $contractRequest->token]) }}"
                                           target="_blank"
                                           rel="noopener">
                                            Download PDF
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex flex-wrap gap-2">
                                    <a class="inline-flex items-center rounded-lg border border-primary-300 bg-primary-50 px-3 py-1.5 text-xs font-semibold text-primary-700 hover:bg-primary-100"
                                       href="{{ url('/admin/onboarding-workspace?onboarding=' . $row->id) }}">
                                        Open Workspace
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-6 text-center text-slate-500">No onboarding records found for current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

    @if ($showAuditTrailModal)
        <div class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-950/50 p-4" wire:key="signature-audit-modal">
            <div class="w-full max-w-5xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Signature Audit Trail</h3>
                        <p class="text-sm text-slate-500">{{ $auditTrailEmployeeName }}</p>
                    </div>
                    <button type="button" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50" wire:click="closeAuditTrail">
                        Close
                    </button>
                </div>

                <div class="max-h-[70vh] overflow-auto p-5">
                    <div class="mb-4 grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 md:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Event Type</label>
                            <select wire:model.live="auditEventFilter" class="w-full rounded-lg border-slate-300 text-sm">
                                <option value="">All events</option>
                                @foreach ($this->getAuditEventOptions() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">From Date</label>
                            <input type="date" wire:model.live="auditFromDate" class="w-full rounded-lg border-slate-300 text-sm" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">To Date</label>
                            <input type="date" wire:model.live="auditToDate" class="w-full rounded-lg border-slate-300 text-sm" />
                        </div>
                        <div class="flex items-end">
                            <x-filament::button size="sm" color="gray" wire:click="clearAuditTrailFilters">Reset Filters</x-filament::button>
                        </div>
                    </div>

                    @php($filteredAuditTrailEntries = $this->getFilteredAuditTrailEntries())

                    @if (empty($filteredAuditTrailEntries))
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-600">
                            No signature audit events match the selected filters.
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-800">When</th>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-800">Document</th>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-800">Event</th>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-800">IP</th>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-800">User Agent</th>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-800">Payload</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach ($filteredAuditTrailEntries as $entry)
                                        <tr>
                                            <td class="px-3 py-2 align-top text-xs text-slate-600">{{ $entry['at'] }}</td>
                                            <td class="px-3 py-2 align-top text-xs text-slate-700">
                                                <div class="font-medium">{{ strtoupper($entry['document_type']) }}</div>
                                                <div class="text-slate-500">Req #{{ $entry['request_id'] }}</div>
                                            </td>
                                            <td class="px-3 py-2 align-top text-xs text-slate-700">{{ str($entry['event_type'])->replace('_', ' ')->title() }}</td>
                                            <td class="px-3 py-2 align-top text-xs text-slate-600">{{ $entry['ip'] }}</td>
                                            <td class="px-3 py-2 align-top text-xs text-slate-600">{{ $entry['user_agent'] }}</td>
                                            <td class="px-3 py-2 align-top text-xs text-slate-600">{{ $entry['payload'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if ($showReferenceResponsesModal)
        @php($responses = collect($referenceResponses))
        @php($verifiedCount = $responses->where('is_verified', true)->count())
        @php($respondedCount = $responses->where('responded_at', '!=', '—')->count())
        @php($pendingCount = max($responses->count() - $verifiedCount, 0))
        <div class="fixed inset-0 z-[9998] bg-slate-950/60 backdrop-blur-sm" wire:key="reference-responses-modal">
            <div class="absolute inset-0" wire:click="closeReferenceResponses"></div>

            <div class="relative mx-auto mt-8 w-[min(1080px,96vw)] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="border-b border-slate-200 bg-gradient-to-r from-indigo-50 via-white to-emerald-50 px-6 py-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-900">Referee Responses</h3>
                            <p class="text-sm text-slate-600">{{ $referenceResponsesEmployeeName }}</p>
                        </div>
                        <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50" wire:click="closeReferenceResponses">
                            Close
                        </button>
                    </div>

                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Referees</p>
                            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $responses->count() }}</p>
                        </div>
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Verified</p>
                            <p class="mt-1 text-2xl font-bold text-emerald-800">{{ $verifiedCount }}</p>
                        </div>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Pending Review</p>
                            <p class="mt-1 text-2xl font-bold text-amber-800">{{ $pendingCount }}</p>
                        </div>
                    </div>
                </div>

                <div class="max-h-[70vh] space-y-3 overflow-y-auto bg-slate-50 p-5">
                    @if ($responses->isEmpty())
                        <div class="rounded-xl border border-slate-200 bg-white p-5 text-sm text-slate-600">
                            No referee records found for this employee.
                        </div>
                    @else
                        @foreach ($referenceResponses as $entry)
                            @php($statusLabel = str($entry['request_status'])->replace('_', ' ')->title())
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="grid gap-4 md:grid-cols-12">
                                    <div class="md:col-span-3">
                                        <p class="text-base font-semibold text-slate-900">{{ $entry['referee_name'] }}</p>
                                        <p class="text-xs text-slate-500">{{ $entry['relationship'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $entry['email'] }}</p>
                                    </div>

                                    <div class="md:col-span-2">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Request</p>
                                        <div class="mt-1 inline-flex rounded-full bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-700">
                                            {{ $statusLabel }}
                                        </div>
                                        <p class="mt-2 text-xs text-slate-500">Requested: {{ $entry['requested_at'] }}</p>
                                    </div>

                                    <div class="md:col-span-2">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Response</p>
                                        <p class="mt-1 text-xs text-slate-700">Responded: {{ $entry['responded_at'] }}</p>
                                        <p class="text-xs text-slate-700">Rating: {{ $entry['response_rating'] }}</p>
                                    </div>

                                    <div class="md:col-span-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Comments</p>
                                        <p class="mt-1 text-xs leading-relaxed text-slate-700">{{ $entry['comments'] }}</p>
                                    </div>

                                    <div class="md:col-span-2">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Verification</p>
                                        @if ($entry['is_verified'])
                                            <span class="mt-1 inline-flex rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Verified</span>
                                        @else
                                            <span class="mt-1 inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">Pending</span>
                                        @endif
                                        <p class="mt-2 text-xs text-slate-500">At: {{ $entry['verified_at'] }}</p>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <x-filament::button size="xs" color="success" wire:click="verifyReference({{ $entry['id'] }})">Verify</x-filament::button>
                                            <x-filament::button size="xs" color="danger" wire:click="openRejectReferenceModal({{ $entry['id'] }})">Reject</x-filament::button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if ($showRejectReferenceModal)
        <div class="fixed inset-0 z-[130] flex items-center justify-center bg-slate-950/50 p-4" wire:key="reject-reference-modal">
            <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Reject Reference</h3>
                    <button type="button" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50" wire:click="closeRejectReferenceModal">
                        Close
                    </button>
                </div>
                <div class="p-5">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Rejection note <span class="text-rose-600">*</span></label>
                    <textarea
                        wire:model.defer="rejectReferenceReason"
                        class="w-full rounded-lg border-slate-300 text-sm"
                        rows="4"
                        placeholder="State why this referee response was rejected"></textarea>
                    <p class="mt-2 text-xs text-slate-500">This note is saved in the verification feedback audit trail.</p>
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-5 py-4">
                    <x-filament::button size="sm" color="gray" wire:click="closeRejectReferenceModal">Cancel</x-filament::button>
                    <x-filament::button size="sm" color="danger" wire:click="rejectReference">Reject With Note</x-filament::button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
