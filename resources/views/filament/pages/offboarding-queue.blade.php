<x-filament-panels::page>
    @php($summary = $this->getSummary())

    <x-filament::section heading="Offboarding Queue">
        <div class="grid gap-3 md:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Active</div>
                <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($summary['active']) }}</div>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <div class="text-xs font-medium uppercase tracking-wide text-amber-700">Due This Week</div>
                <div class="mt-1 text-2xl font-bold text-amber-900">{{ number_format($summary['due_this_week']) }}</div>
            </div>
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                <div class="text-xs font-medium uppercase tracking-wide text-rose-700">Overdue</div>
                <div class="mt-1 text-2xl font-bold text-rose-900">{{ number_format($summary['overdue']) }}</div>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <div class="text-xs font-medium uppercase tracking-wide text-emerald-700">Completed This Month</div>
                <div class="mt-1 text-2xl font-bold text-emerald-900">{{ number_format($summary['completed_this_month']) }}</div>
            </div>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Search</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    class="w-full rounded-lg border-slate-300 text-sm"
                    placeholder="Employee, ID, reason"
                />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                <select wire:model.live="statusFilter" class="w-full rounded-lg border-slate-300 text-sm">
                    @foreach ($this->getStatusOptions() as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Due Window</label>
                <select wire:model.live="dueFilter" class="w-full rounded-lg border-slate-300 text-sm">
                    @foreach ($this->getDueFilterOptions() as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-filament::section>

    @php($rows = $this->getRows())

    <x-filament::section heading="Queue">
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-slate-800">Employee</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-800">Status</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-800">Last Day</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-800">Checklist</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-800">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($rows as $row)
                        @php($user = $row->user)
                        <tr>
                            <td class="px-3 py-2 align-top">
                                <div class="font-medium text-slate-900">{{ $user?->name ?: $row->user_id }}</div>
                                <div class="text-xs text-slate-500">{{ $row->user_id }}</div>
                                <div class="text-xs text-slate-500">{{ $user?->department ?: 'Unassigned' }} · {{ $user?->position ?: 'No role' }}</div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold
                                    {{ $row->offboarding_status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                    {{ $row->offboarding_status === 'in_progress' ? 'bg-indigo-100 text-indigo-700' : '' }}
                                    {{ $row->offboarding_status === 'planned' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $row->offboarding_status === 'cancelled' ? 'bg-slate-200 text-slate-700' : '' }}
                                    {{ $row->offboarding_status === 'not_started' ? 'bg-slate-100 text-slate-700' : '' }}
                                ">
                                    {{ str($row->offboarding_status)->replace('_', ' ')->title() }}
                                </span>
                                <div class="mt-1 text-xs text-slate-500">{{ $row->offboarding_type ? str($row->offboarding_type)->replace('_', ' ')->title() : 'Type not set' }}</div>
                            </td>
                            <td class="px-3 py-2 align-top text-slate-700">
                                {{ optional($row->last_working_day)->format('M j, Y') ?: 'Not set' }}
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex flex-wrap gap-1">
                                    <span class="rounded-full px-2 py-1 text-xs {{ $row->knowledge_transfer_completed ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">KT</span>
                                    <span class="rounded-full px-2 py-1 text-xs {{ $row->assets_returned ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">Assets</span>
                                    <span class="rounded-full px-2 py-1 text-xs {{ $row->access_revoked ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">Access</span>
                                    <span class="rounded-full px-2 py-1 text-xs {{ $row->final_settlement_completed ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">Settlement</span>
                                    <span class="rounded-full px-2 py-1 text-xs {{ $row->exit_interview_completed ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">Exit Interview</span>
                                </div>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <div class="flex flex-wrap gap-2">
                                    <x-filament::button size="xs" color="gray" wire:click="toggleChecklist({{ $row->id }}, 'assets_returned')">Toggle Assets</x-filament::button>
                                    <x-filament::button size="xs" color="gray" wire:click="toggleChecklist({{ $row->id }}, 'access_revoked')">Toggle Access</x-filament::button>
                                    <x-filament::button size="xs" color="gray" wire:click="toggleChecklist({{ $row->id }}, 'final_settlement_completed')">Toggle Settlement</x-filament::button>
                                    <x-filament::button size="xs" color="success" wire:click="markCompleted({{ $row->id }})">Mark Completed</x-filament::button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-slate-500">No offboarding records match the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>

