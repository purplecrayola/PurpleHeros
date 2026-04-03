<x-filament-panels::page>
    @php
        $activePolicyBands = \App\Models\LeavePolicyBand::query()->where('is_active', true)->count();
        $openRequests = \App\Models\LeavesAdmin::query()->where('status', 'Pending')->count();
        $holidayCount = \App\Models\Holiday::query()->count();
    @endphp

    <div
        class="rounded-2xl p-6 shadow-sm ring-1 ring-black/10 md:p-8"
        style="background: linear-gradient(110deg, #00163F 0%, #2A1A70 50%, #8A00FF 100%); color: #ffffff;"
    >
        <p class="text-xs font-semibold uppercase tracking-[0.22em]" style="color: rgba(255, 255, 255, 0.82);">Time & Attendance</p>
        <h2 class="mt-2 text-2xl font-bold md:text-3xl" style="color: #ffffff;">Leave Settings Command Center</h2>
        <p class="mt-2 max-w-3xl text-sm md:text-base" style="color: rgba(255, 255, 255, 0.94);">
            Manage policy bands, approvals, holiday calendars, and leave balance visibility from one place.
        </p>
        <div class="mt-5 grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl px-4 py-3" style="border: 1px solid rgba(255, 255, 255, 0.22); background-color: rgba(255, 255, 255, 0.14);">
                <p class="text-xs uppercase tracking-wide" style="color: rgba(255, 255, 255, 0.84);">Active Leave Bands</p>
                <p class="text-xl font-semibold" style="color: #ffffff;">{{ number_format((int) $activePolicyBands) }}</p>
            </div>
            <div class="rounded-xl px-4 py-3" style="border: 1px solid rgba(255, 255, 255, 0.22); background-color: rgba(255, 255, 255, 0.14);">
                <p class="text-xs uppercase tracking-wide" style="color: rgba(255, 255, 255, 0.84);">Pending Requests</p>
                <p class="text-xl font-semibold" style="color: #ffffff;">{{ number_format((int) $openRequests) }}</p>
            </div>
            <div class="rounded-xl px-4 py-3" style="border: 1px solid rgba(255, 255, 255, 0.22); background-color: rgba(255, 255, 255, 0.14);">
                <p class="text-xs uppercase tracking-wide" style="color: rgba(255, 255, 255, 0.84);">Holiday Entries</p>
                <p class="text-xl font-semibold" style="color: #ffffff;">{{ number_format((int) $holidayCount) }}</p>
            </div>
        </div>
    </div>

    <x-filament::section heading="Primary Actions">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <a href="{{ url('/admin/leave-policy-bands') }}" class="group rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-[#8A00FF]/40 hover:shadow-md">
                <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#8A00FF]/10 text-[#8A00FF] ring-1 ring-[#8A00FF]/20">
                    <x-heroicon-o-adjustments-horizontal class="h-5 w-5" />
                </div>
                <p class="font-semibold text-slate-900">Leave Policy Bands</p>
                <p class="mt-1 text-sm text-slate-600">Set yearly entitlements and carry-forward caps per leave type.</p>
                <p class="mt-3 text-xs font-medium text-[#8A00FF] group-hover:text-[#6E00CC]">Open module →</p>
            </a>
            <a href="{{ url('/admin/leaves-admins') }}" class="group rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-[#8A00FF]/40 hover:shadow-md">
                <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#8A00FF]/10 text-[#8A00FF] ring-1 ring-[#8A00FF]/20">
                    <x-heroicon-o-document-check class="h-5 w-5" />
                </div>
                <p class="font-semibold text-slate-900">Leave Requests</p>
                <p class="mt-1 text-sm text-slate-600">Review, approve, or reject employee leave records.</p>
                <p class="mt-3 text-xs font-medium text-[#8A00FF] group-hover:text-[#6E00CC]">Open module →</p>
            </a>
            <a href="{{ url('/admin/holidays') }}" class="group rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-[#8A00FF]/40 hover:shadow-md">
                <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#8A00FF]/10 text-[#8A00FF] ring-1 ring-[#8A00FF]/20">
                    <x-heroicon-o-calendar-days class="h-5 w-5" />
                </div>
                <p class="font-semibold text-slate-900">Holiday Calendar</p>
                <p class="mt-1 text-sm text-slate-600">Maintain company holidays used for attendance context.</p>
                <p class="mt-3 text-xs font-medium text-[#8A00FF] group-hover:text-[#6E00CC]">Open module →</p>
            </a>
            <a href="{{ url('/admin/leave-balance-report') }}" class="group rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-[#8A00FF]/40 hover:shadow-md">
                <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#8A00FF]/10 text-[#8A00FF] ring-1 ring-[#8A00FF]/20">
                    <x-heroicon-o-presentation-chart-line class="h-5 w-5" />
                </div>
                <p class="font-semibold text-slate-900">Leave Balance Report</p>
                <p class="mt-1 text-sm text-slate-600">Track entitlement, usage, and remaining balance by employee.</p>
                <p class="mt-3 text-xs font-medium text-[#8A00FF] group-hover:text-[#6E00CC]">Open module →</p>
            </a>
        </div>
    </x-filament::section>
</x-filament-panels::page>
