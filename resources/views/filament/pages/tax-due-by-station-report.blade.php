<x-filament-panels::page>
    @php
        $stationRows = $this->getStationRows();
        $employeeRows = $this->getEmployeeRows();
        $months = $this->getMonths();
        $years = $this->getYears();
        $stationOptions = $this->getStationOptions();
        $periodLabel = date('F', mktime(0, 0, 0, (int) $this->reportMonth, 1)) . ' ' . (int) $this->reportYear;

        $totalPaye = (float) collect($stationRows)->sum('paye_due');
        $totalEmployees = (int) collect($stationRows)->sum('employee_count');
        $stationCount = (int) collect($stationRows)->count();
        $avgPayePerEmployee = $totalEmployees > 0 ? ($totalPaye / $totalEmployees) : 0.0;
    @endphp

    <x-filament::section>
        <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
            <div class="max-w-3xl">
                <h2 class="text-3xl font-semibold tracking-tight text-slate-900">Tax Due By Tax Station</h2>
                <p class="mt-2 text-base text-slate-600">Monthly PAYE visibility by station and employee for {{ $periodLabel }}.</p>
            </div>
            <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">
                    Payroll Tax Intelligence
                </span>
        </div>

        <div class="pc-filter-shell">
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Year</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="reportYear">
                        @foreach($years as $yearValue => $yearLabel)
                            <option value="{{ $yearValue }}">{{ $yearLabel }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Month</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="reportMonth">
                        @foreach($months as $monthValue => $monthLabel)
                            <option value="{{ $monthValue }}">{{ $monthLabel }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Tax Station</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="selectedTaxStation">
                        @foreach($stationOptions as $stationValue => $stationLabel)
                            <option value="{{ $stationValue }}">{{ $stationLabel }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                </div>
            </div>
        </div>

        <div class="mt-5 grid gap-3" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
            <div class="rounded-2xl bg-slate-900 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-300">Total PAYE Due</p>
                <p class="mt-2 text-4xl font-semibold tracking-tight text-white">₦{{ number_format($totalPaye, 2) }}</p>
                <p class="mt-2 text-xs text-slate-300">{{ $periodLabel }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Employees</p>
                <p class="mt-2 text-4xl font-semibold tracking-tight text-slate-900">{{ number_format($totalEmployees) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tax Stations</p>
                <p class="mt-2 text-4xl font-semibold tracking-tight text-slate-900">{{ number_format($stationCount) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Average PAYE / Employee</p>
                <p class="mt-2 text-4xl font-semibold tracking-tight text-slate-900">₦{{ number_format($avgPayePerEmployee, 2) }}</p>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-xl font-semibold tracking-tight text-slate-900">Station Summary</h3>
                <p class="text-sm text-slate-500">PAYE totals grouped by tax station.</p>
            </div>
            <div class="pc-export-wrap">
                <x-filament::button size="sm" color="gray" wire:click="exportStationSummary('csv')">CSV</x-filament::button>
                <x-filament::button size="sm" color="gray" wire:click="exportStationSummary('xlsx')">XLSX</x-filament::button>
                <x-filament::button size="sm" color="gray" wire:click="exportStationSummary('pdf')">PDF</x-filament::button>
            </div>
        </div>
        <div class="mb-5 rounded-2xl border border-indigo-100 bg-indigo-50/70 px-4 py-3 text-sm text-indigo-900">
            PAYE due for <span class="font-semibold">{{ $periodLabel }}</span>:
            <span class="font-semibold">₦{{ number_format($totalPaye, 2) }}</span>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/80 text-left">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Tax Station</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Employees</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">PAYE Due (NGN)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($stationRows as $row)
                            <tr class="odd:bg-white even:bg-slate-50/35 hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium text-slate-900">{{ $row['tax_station'] }}</td>
                                <td class="px-5 py-3 text-slate-700">{{ number_format((int) $row['employee_count']) }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-slate-900">₦{{ number_format((float) $row['paye_due'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-5 pc-empty-state" colspan="3">No tax records found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-xl font-semibold tracking-tight text-slate-900">Per Employee Breakdown</h3>
                <p class="text-sm text-slate-500">Detailed payroll tax and net salary line items.</p>
            </div>
            <div class="pc-export-wrap">
                <x-filament::button size="sm" color="gray" wire:click="exportEmployeeBreakdown('csv')">CSV</x-filament::button>
                <x-filament::button size="sm" color="gray" wire:click="exportEmployeeBreakdown('xlsx')">XLSX</x-filament::button>
                <x-filament::button size="sm" color="gray" wire:click="exportEmployeeBreakdown('pdf')">PDF</x-filament::button>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full table-fixed divide-y divide-slate-200 text-sm">
                    <colgroup>
                        <col style="width: 28%;" />
                        <col style="width: 10%;" />
                        <col style="width: 12%;" />
                        <col style="width: 13%;" />
                        <col style="width: 17%;" />
                        <col style="width: 20%;" />
                    </colgroup>
                    <thead class="bg-slate-50/80 text-left">
                        <tr>
                            <th class="whitespace-nowrap px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Employee</th>
                            <th class="whitespace-nowrap px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Tax Station</th>
                            <th class="whitespace-nowrap px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Run Code</th>
                            <th class="whitespace-nowrap px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">PAYE (NGN)</th>
                            <th class="whitespace-nowrap px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Total Deductions (NGN)</th>
                            <th class="whitespace-nowrap px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Net Salary (NGN)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($employeeRows as $row)
                            <tr class="odd:bg-white even:bg-slate-50/35 hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <div class="truncate font-medium text-slate-900">{{ $row['employee_name'] }}</div>
                                    <div class="text-xs text-slate-500">{{ $row['employee_id'] }}</div>
                                </td>
                                <td class="px-5 py-3 text-slate-700">{{ $row['tax_station'] }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-slate-700">{{ $row['run_code'] }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right font-semibold text-slate-900">₦{{ number_format((float) $row['paye_due'], 2) }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right text-slate-700">₦{{ number_format((float) $row['total_deductions'], 2) }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right font-semibold text-emerald-700">₦{{ number_format((float) $row['net_salary'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-5 pc-empty-state" colspan="6">No employee rows found for this filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
