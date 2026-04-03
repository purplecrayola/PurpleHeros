<x-filament-panels::page>
    @php($row = $onboarding)
    @php($offerRequest = $row?->latestOfferSignatureRequest)
    @php($contractRequest = $row?->latestContractSignatureRequest)

    <x-filament::section>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Onboarding Workspace</h2>
                <p class="text-sm text-slate-600">{{ $row?->user?->name ?: $row?->user_id }}</p>
                <p class="text-xs text-slate-500">{{ $row?->user?->email ?: 'No email' }} · {{ $row?->user?->department ?: 'No department' }}</p>
            </div>
            <a href="{{ url('/admin/onboarding-queue') }}" class="inline-flex rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Back to Queue
            </a>
        </div>
    </x-filament::section>

    <x-filament::section heading="Signature Workflow">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-slate-900">Offer</h3>
                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">{{ str($row?->offer_status)->replace('_', ' ')->title() }}</span>
                </div>
                <p class="mt-2 text-xs text-slate-500">Sent: {{ optional($row?->offer_sent_at)->format('M j, Y') ?: '—' }} · Signed: {{ optional($row?->offer_signed_at)->format('M j, Y') ?: '—' }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a class="inline-flex items-center rounded-lg border border-slate-300 px-2 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50"
                       href="{{ url('/admin/signature-field-mapper?onboarding=' . $row?->id . '&document=offer') }}">
                        Map Offer Fields
                    </a>
                    <x-filament::button size="xs" color="gray" wire:click="markOfferSent">Send Offer Link</x-filament::button>
                    <x-filament::button size="xs" color="success" wire:click="markOfferSigned">Mark Offer Signed</x-filament::button>
                    @if ($offerRequest?->signed_document_path)
                        <a class="inline-flex items-center rounded-lg border border-primary-300 bg-primary-50 px-2 py-1 text-xs font-medium text-primary-700 hover:bg-primary-100"
                           href="{{ route('signature/request/download-signed', ['token' => $offerRequest->token]) }}"
                           target="_blank"
                           rel="noopener">
                            Download Offer PDF
                        </a>
                    @endif
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-slate-900">Contract</h3>
                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">{{ str($row?->contract_status)->replace('_', ' ')->title() }}</span>
                </div>
                <p class="mt-2 text-xs text-slate-500">Sent: {{ optional($row?->contract_sent_at)->format('M j, Y') ?: '—' }} · Signed: {{ optional($row?->contract_signed_at)->format('M j, Y') ?: '—' }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a class="inline-flex items-center rounded-lg border border-slate-300 px-2 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50"
                       href="{{ url('/admin/signature-field-mapper?onboarding=' . $row?->id . '&document=contract') }}">
                        Map Contract Fields
                    </a>
                    <x-filament::button size="xs" color="gray" wire:click="markContractSent">Send Contract Link</x-filament::button>
                    <x-filament::button size="xs" color="success" wire:click="markContractSigned">Mark Contract Signed</x-filament::button>
                    @if ($contractRequest?->signed_document_path)
                        <a class="inline-flex items-center rounded-lg border border-primary-300 bg-primary-50 px-2 py-1 text-xs font-medium text-primary-700 hover:bg-primary-100"
                           href="{{ route('signature/request/download-signed', ['token' => $contractRequest->token]) }}"
                           target="_blank"
                           rel="noopener">
                            Download Contract PDF
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section heading="Reference Checks">
        <div class="flex flex-wrap gap-2">
            <x-filament::button size="sm" color="warning" wire:click="sendReferenceRequests">Send Referee Requests</x-filament::button>
            <x-filament::button size="sm" color="info" wire:click="openReferenceResponses">Review Referee Responses</x-filament::button>
            <x-filament::button size="sm" color="gray" wire:click="openAuditTrail">View Signature Audit Trail</x-filament::button>
            <x-filament::button size="sm" color="success" wire:click="markOnboarded">Mark Onboarded</x-filament::button>
        </div>
        <div class="mt-3 text-xs text-slate-600">
            Status: <span class="font-semibold">{{ str($row?->reference_check_status)->replace('_', ' ')->title() }}</span>
            · Verified {{ (int) ($row?->references_verified_count ?? 0) }} / {{ (int) ($row?->references_total_count ?? 0) }}
        </div>
    </x-filament::section>

    @if ($showReferenceResponsesModal)
        @php($responses = collect($referenceResponses))
        @php($verifiedCount = $responses->where('is_verified', true)->count())
        @php($pendingCount = max($responses->count() - $verifiedCount, 0))
        <div class="fixed inset-0 z-[9998] bg-slate-950/60 backdrop-blur-sm" wire:key="reference-responses-modal">
            <div class="absolute inset-0" wire:click="closeReferenceResponses"></div>
            <div class="relative mx-auto mt-8 w-[min(1080px,96vw)] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="border-b border-slate-200 bg-gradient-to-r from-indigo-50 via-white to-emerald-50 px-6 py-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-900">Referee Responses</h3>
                            <p class="text-sm text-slate-600">{{ $row?->user?->name ?: $row?->user_id }}</p>
                        </div>
                        <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50" wire:click="closeReferenceResponses">Close</button>
                    </div>
                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total</p><p class="mt-1 text-2xl font-bold text-slate-900">{{ $responses->count() }}</p></div>
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3"><p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Verified</p><p class="mt-1 text-2xl font-bold text-emerald-800">{{ $verifiedCount }}</p></div>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3"><p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Pending</p><p class="mt-1 text-2xl font-bold text-amber-800">{{ $pendingCount }}</p></div>
                    </div>
                </div>
                <div class="max-h-[70vh] space-y-3 overflow-y-auto bg-slate-50 p-5">
                    @foreach ($referenceResponses as $entry)
                        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="grid gap-4 md:grid-cols-12">
                                <div class="md:col-span-3">
                                    <p class="text-base font-semibold text-slate-900">{{ $entry['referee_name'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $entry['relationship'] }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $entry['email'] }}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Request</p>
                                    <div class="mt-1 inline-flex rounded-full bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-700">{{ str($entry['request_status'])->replace('_', ' ')->title() }}</div>
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
                </div>
            </div>
        </div>
    @endif

    @if ($showRejectReferenceModal)
        <div class="fixed inset-0 z-[10000] flex items-center justify-center bg-slate-950/50 p-4" wire:key="reject-reference-modal">
            <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Reject Reference</h3>
                    <button type="button" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50" wire:click="closeRejectReferenceModal">Close</button>
                </div>
                <div class="p-5">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Rejection note <span class="text-rose-600">*</span></label>
                    <textarea wire:model.defer="rejectReferenceReason" class="w-full rounded-lg border-slate-300 text-sm" rows="4" placeholder="State why this referee response was rejected"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-5 py-4">
                    <x-filament::button size="sm" color="gray" wire:click="closeRejectReferenceModal">Cancel</x-filament::button>
                    <x-filament::button size="sm" color="danger" wire:click="rejectReference">Reject With Note</x-filament::button>
                </div>
            </div>
        </div>
    @endif

    @if ($showAuditTrailModal)
        <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/50 p-4" wire:key="signature-audit-modal">
            <div class="w-full max-w-5xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Signature Audit Trail</h3>
                    <button type="button" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50" wire:click="closeAuditTrail">Close</button>
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
                                        <td class="px-3 py-2 align-top text-xs text-slate-700">{{ strtoupper($entry['document_type']) }} · Req #{{ $entry['request_id'] }}</td>
                                        <td class="px-3 py-2 align-top text-xs text-slate-700">{{ str($entry['event_type'])->replace('_', ' ')->title() }}</td>
                                        <td class="px-3 py-2 align-top text-xs text-slate-600">{{ $entry['ip'] }}</td>
                                        <td class="px-3 py-2 align-top text-xs text-slate-600">{{ $entry['user_agent'] }}</td>
                                        <td class="px-3 py-2 align-top text-xs text-slate-600">{{ $entry['payload'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>

