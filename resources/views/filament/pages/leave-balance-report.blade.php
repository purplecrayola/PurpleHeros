<x-filament-panels::page>
    <x-filament::section heading="Leave Balance by Employee">
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <x-filament::button size="sm" color="gray" wire:click="exportBalance('csv')">Export CSV</x-filament::button>
            <x-filament::button size="sm" color="gray" wire:click="exportBalance('pdf')">Export PDF</x-filament::button>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Year</label>
                <select wire:model.live="year" class="w-full rounded-lg border-slate-300 text-sm">
                    @foreach ($this->getYearOptions() as $yearValue => $yearLabel)
                        <option value="{{ $yearValue }}">{{ $yearLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Department</label>
                <select wire:model.live="department" class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="">All departments</option>
                    @foreach ($this->getDepartmentOptions() as $deptValue => $deptLabel)
                        <option value="{{ $deptValue }}">{{ $deptLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Search employee</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    class="w-full rounded-lg border-slate-300 text-sm"
                    placeholder="Name or User ID"
                />
            </div>
        </div>
    </x-filament::section>

    @php
        $bands = $this->getBands();
        $rows = $this->getRows();
    @endphp

    <x-filament::section heading="Balances">
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th rowspan="2" class="px-3 py-2 text-left font-semibold text-slate-800">Employee</th>
                        <th rowspan="2" class="px-3 py-2 text-left font-semibold text-slate-800">Department</th>
                        <th rowspan="2" class="px-3 py-2 text-left font-semibold text-slate-800">Position</th>
                        @foreach ($bands as $band)
                            <th colspan="3" class="px-3 py-2 text-center font-semibold text-slate-800">{{ $band->name }}</th>
                        @endforeach
                        <th rowspan="2" class="px-3 py-2 text-right font-semibold text-slate-800">Total Used</th>
                    </tr>
                    <tr>
                        @foreach ($bands as $band)
                            <th class="px-3 py-2 text-right font-medium text-slate-600">Ent.</th>
                            <th class="px-3 py-2 text-right font-medium text-slate-600">Used</th>
                            <th class="px-3 py-2 text-right font-medium text-slate-600">Remain</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($rows as $row)
                        <tr>
                            <td class="px-3 py-2">
                                <div class="font-medium text-slate-900">{{ $row->name }}</div>
                                <div class="text-xs text-slate-500">{{ $row->user_id }}</div>
                            </td>
                            <td class="px-3 py-2 text-slate-700">{{ $row->department }}</td>
                            <td class="px-3 py-2 text-slate-700">{{ $row->position }}</td>
                            @foreach ($bands as $band)
                                @php $cell = $row->bands[$band->name]; @endphp
                                <td class="px-3 py-2 text-right text-slate-700">{{ $cell['entitlement'] === null ? 'Uncapped' : number_format((float) $cell['entitlement'], 1) }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ number_format((float) $cell['used'], 1) }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ $cell['remaining'] === null ? 'N/A' : number_format((float) $cell['remaining'], 1) }}</td>
                            @endforeach
                            <td class="px-3 py-2 text-right font-semibold text-slate-900">{{ number_format((float) $row->total_used, 1) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 4 + ($bands->count() * 3) }}" class="px-3 py-6 text-center text-slate-500">
                                No employees found for the current filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
