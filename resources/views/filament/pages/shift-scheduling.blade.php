<x-filament-panels::page>
    <x-filament::section heading="Shift Operations">
        <div class="space-y-3 text-sm text-slate-600">
            <p>
                Legacy shift scheduling and shift list pages were static templates without persistent scheduling logic.
            </p>
            <p>
                Use the operational modules below for daily workforce tracking while full shift planning is being productized.
            </p>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-3">
            <a href="{{ url('/admin/attendance-records') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-3 hover:bg-slate-50">
                <p class="font-medium text-slate-900">Attendance Records</p>
                <p class="text-sm text-slate-500">Review daily present/late/absent records.</p>
            </a>
            <a href="{{ url('/admin/timesheet-entries') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-3 hover:bg-slate-50">
                <p class="font-medium text-slate-900">Timesheets</p>
                <p class="text-sm text-slate-500">Capture and validate worked hours by day.</p>
            </a>
            <a href="{{ url('/admin/overtime-entries') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-3 hover:bg-slate-50">
                <p class="font-medium text-slate-900">Overtime</p>
                <p class="text-sm text-slate-500">Track approved overtime for payroll.</p>
            </a>
        </div>
    </x-filament::section>

    <x-filament::section heading="Roadmap Note">
        <p class="text-sm text-slate-600">
            Next enhancement: shift templates, assignment calendar, and recurring roster rules with role/department constraints.
        </p>
    </x-filament::section>
</x-filament-panels::page>

