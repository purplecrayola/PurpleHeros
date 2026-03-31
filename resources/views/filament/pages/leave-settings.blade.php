<x-filament-panels::page>
    <x-filament::section heading="Leave Policy Controls">
        <div class="space-y-3 text-sm text-slate-600">
            <p>Configure leave policy bands (annual, sick, maternity, unpaid), then process leave requests against those policy limits.</p>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <a href="{{ url('/admin/leave-policy-bands') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-3 hover:bg-slate-50">
                <p class="font-medium text-slate-900">Leave Policy Bands</p>
                <p class="text-sm text-slate-500">Set yearly entitlements and carry-forward caps per leave type.</p>
            </a>
            <a href="{{ url('/admin/leaves-admins') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-3 hover:bg-slate-50">
                <p class="font-medium text-slate-900">Leave Requests</p>
                <p class="text-sm text-slate-500">Review and approve employee leave records.</p>
            </a>
            <a href="{{ url('/admin/holidays') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-3 hover:bg-slate-50">
                <p class="font-medium text-slate-900">Holiday Calendar</p>
                <p class="text-sm text-slate-500">Maintain company holidays used for attendance context.</p>
            </a>
            <a href="{{ url('/admin/leave-balance-report') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-3 hover:bg-slate-50">
                <p class="font-medium text-slate-900">Leave Balance Report</p>
                <p class="text-sm text-slate-500">Track entitlement, usage, and remaining balance by employee.</p>
            </a>
        </div>
    </x-filament::section>

</x-filament-panels::page>
