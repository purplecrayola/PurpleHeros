<x-filament-panels::page>
    @php
        $summary = $this->getSummary();
        $rows = collect($this->getRows())->sortBy([
            ['priority', 'asc'],
            ['status', 'asc'],
            ['label', 'asc'],
        ])->values();
    @endphp

    <x-filament::section>
        <div class="grid gap-4 md:grid-cols-5">
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Tracked Routes</p>
                <p class="text-2xl font-semibold">{{ $summary['total'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Cutover Live</p>
                <p class="text-2xl font-semibold text-emerald-600">{{ $summary['cutover_live'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">In Progress</p>
                <p class="text-2xl font-semibold text-amber-600">{{ $summary['in_progress'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Planned</p>
                <p class="text-2xl font-semibold text-slate-700">{{ $summary['planned'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">No Equivalent</p>
                <p class="text-2xl font-semibold text-rose-600">{{ $summary['no_equivalent'] }}</p>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section heading="Legacy to Filament Migration Map">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left">
                    <tr>
                        <th class="px-3 py-2">Priority</th>
                        <th class="px-3 py-2">Module</th>
                        <th class="px-3 py-2">Legacy Path</th>
                        <th class="px-3 py-2">Filament Path</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Owner</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($rows as $row)
                        <tr>
                            <td class="px-3 py-2">{{ $row['priority'] ?? 'P3' }}</td>
                            <td class="px-3 py-2">{{ $row['label'] }}</td>
                            <td class="px-3 py-2"><code>{{ $row['legacy_path'] }}</code></td>
                            <td class="px-3 py-2">
                                @if(!empty($row['filament_path']))
                                    <code>{{ $row['filament_path'] }}</code>
                                @else
                                    <span class="text-slate-500">Not mapped yet</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <span class="inline-flex rounded-md border px-2 py-1 text-xs font-medium">
                                    {{ str_replace('_', ' ', $row['status']) }}
                                </span>
                            </td>
                            <td class="px-3 py-2">{{ $row['owner'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
