<x-filament-panels::page>
    @php
        $summary = $this->getSummary();
        $employeeRows = $this->getEmployeeRows();
        $leaveRows = $this->getLeaveRows();
        $attendanceRows = $this->getAttendanceRows();
    @endphp

    <x-filament::section>
        <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-7">
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Employees</p>
                <p class="text-2xl font-semibold">{{ $summary['total_employees'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Present</p>
                <p class="text-2xl font-semibold text-emerald-600">{{ $summary['present'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Remote</p>
                <p class="text-2xl font-semibold text-sky-600">{{ $summary['remote'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Late</p>
                <p class="text-2xl font-semibold text-amber-600">{{ $summary['late'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Absent</p>
                <p class="text-2xl font-semibold text-rose-600">{{ $summary['absent'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Leave Requests</p>
                <p class="text-2xl font-semibold">{{ $summary['leave_requests'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Payroll Records</p>
                <p class="text-2xl font-semibold">{{ $summary['payroll_records'] }}</p>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section>
        <div class="pc-filter-shell">
            <div class="pc-filter-grid cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600">Report Date</label>
                <x-filament::input.wrapper>
                    <x-filament::input type="date" wire:model.live="reportDate" />
                </x-filament::input.wrapper>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600">Employee Search</label>
                <x-filament::input.wrapper>
                    <x-filament::input type="text" wire:model.live.debounce.300ms="employeeSearch" placeholder="Name, ID, or email" />
                </x-filament::input.wrapper>
            </div>
            <div class="flex items-end">
                <x-filament::button color="gray" wire:click="$set('employeeSearch', '')">
                    Clear Search
                </x-filament::button>
            </div>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section heading="Employee Report">
        <div class="mb-3 pc-export-wrap">
            <x-filament::button size="sm" color="gray" wire:click="exportEmployees('csv')">Export CSV</x-filament::button>
            <x-filament::button size="sm" color="gray" wire:click="exportEmployees('xlsx')">Export XLSX</x-filament::button>
            <x-filament::button size="sm" color="gray" wire:click="exportEmployees('pdf')">Export PDF</x-filament::button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left">
                    <tr>
                        <th class="px-3 py-2">Employee</th>
                        <th class="px-3 py-2">Department</th>
                        <th class="px-3 py-2">Designation</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Salary</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($employeeRows as $row)
                        <tr>
                            <td class="px-3 py-2">{{ $row->name }} <span class="text-slate-500">({{ $row->user_id }})</span></td>
                            <td class="px-3 py-2">{{ $row->profile_department ?: $row->user_department ?: 'Unassigned' }}</td>
                            <td class="px-3 py-2">{{ $row->designation ?: $row->position ?: 'Not set' }}</td>
                            <td class="px-3 py-2">{{ $row->status ?: 'Active' }}</td>
                            <td class="px-3 py-2">{{ $row->salary ?: 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr><td class="px-3 py-3 pc-empty-state" colspan="5">No employee rows found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section heading="Leave Report">
        <div class="mb-3 pc-export-wrap">
            <x-filament::button size="sm" color="gray" wire:click="exportLeaves('csv')">Export CSV</x-filament::button>
            <x-filament::button size="sm" color="gray" wire:click="exportLeaves('xlsx')">Export XLSX</x-filament::button>
            <x-filament::button size="sm" color="gray" wire:click="exportLeaves('pdf')">Export PDF</x-filament::button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left">
                    <tr>
                        <th class="px-3 py-2">Employee</th>
                        <th class="px-3 py-2">Type</th>
                        <th class="px-3 py-2">Range</th>
                        <th class="px-3 py-2">Days</th>
                        <th class="px-3 py-2">Reason</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($leaveRows as $row)
                        <tr>
                            <td class="px-3 py-2">{{ $row->name }} <span class="text-slate-500">({{ $row->user_id }})</span></td>
                            <td class="px-3 py-2">{{ $row->leave_type }}</td>
                            <td class="px-3 py-2">{{ $row->from_date }} to {{ $row->to_date }}</td>
                            <td class="px-3 py-2">{{ $row->day }}</td>
                            <td class="px-3 py-2">{{ $row->leave_reason ?: 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr><td class="px-3 py-3 pc-empty-state" colspan="5">No leave rows found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section heading="Daily Attendance Report">
        <div class="mb-3 pc-export-wrap">
            <x-filament::button size="sm" color="gray" wire:click="exportAttendance('csv')">Export CSV</x-filament::button>
            <x-filament::button size="sm" color="gray" wire:click="exportAttendance('xlsx')">Export XLSX</x-filament::button>
            <x-filament::button size="sm" color="gray" wire:click="exportAttendance('pdf')">Export PDF</x-filament::button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left">
                    <tr>
                        <th class="px-3 py-2">Employee</th>
                        <th class="px-3 py-2">Date</th>
                        <th class="px-3 py-2">Department</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Check In</th>
                        <th class="px-3 py-2">Check Out</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($attendanceRows as $row)
                        <tr>
                            <td class="px-3 py-2">{{ $row->name }} <span class="text-slate-500">({{ $row->user_id }})</span></td>
                            <td class="px-3 py-2">{{ $row->attendance_date }}</td>
                            <td class="px-3 py-2">{{ $row->department ?: 'Unassigned' }}</td>
                            <td class="px-3 py-2">{{ ucfirst($row->status) }}</td>
                            <td class="px-3 py-2">{{ $row->check_in ?: 'N/A' }}</td>
                            <td class="px-3 py-2">{{ $row->check_out ?: 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr><td class="px-3 py-3 pc-empty-state" colspan="6">No attendance rows found for selected date.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
