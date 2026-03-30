<x-filament-panels::page>
    <x-filament::section heading="Leave Policy Controls">
        <div class="space-y-3 text-sm text-slate-600">
            <p>
                Legacy leave settings was a static template. This page is now the operational home for leave policy management.
            </p>
            <p>
                Current live controls are handled through policy sets and leave request governance in the modules below.
            </p>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-2">
            <a href="{{ url('/admin/leaves-admins') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-3 hover:bg-slate-50">
                <p class="font-medium text-slate-900">Leave Requests</p>
                <p class="text-sm text-slate-500">Review and approve employee leave records.</p>
            </a>
            <a href="{{ url('/admin/holidays') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-3 hover:bg-slate-50">
                <p class="font-medium text-slate-900">Holiday Calendar</p>
                <p class="text-sm text-slate-500">Maintain company holidays used for attendance context.</p>
            </a>
        </div>
    </x-filament::section>

    <x-filament::section heading="Roadmap Note">
        <p class="text-sm text-slate-600">
            Next enhancement: structured leave policy bands (annual, sick, maternity, carry-forward limits) as first-class data entities.
        </p>
    </x-filament::section>
</x-filament-panels::page>

