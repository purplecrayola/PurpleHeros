<x-filament-panels::page>
    <x-filament::section heading="Current Product Scope">
        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">DA / HRA Rules</p>
                <p class="text-lg font-semibold">Manual</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Payslip Output</p>
                <p class="text-lg font-semibold">Enabled</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Payroll Engine</p>
                <p class="text-lg font-semibold">Basic</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Country Fit</p>
                <p class="text-lg font-semibold">Single-country</p>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section heading="Available Modules">
        <ul class="list-disc space-y-1 pl-5 text-sm text-slate-700">
            <li>Net salary, earnings, and deduction components are stored per employee salary record.</li>
            <li>Payslips are generated from payroll runs / salary records.</li>
            <li>Policy sets are managed in Payroll Policy Sets.</li>
        </ul>
        <div class="mt-4 flex flex-wrap gap-3">
            <a href="/admin/payroll-policy-sets" class="fi-btn fi-btn-size-md fi-color-primary fi-btn-color-primary fi-ac-action fi-ac-btn-action">Open Payroll Policy Sets</a>
            <a href="/admin/payroll-runs" class="fi-btn fi-btn-size-md fi-color-gray fi-btn-color-gray fi-ac-action fi-ac-btn-action">Open Payroll Runs</a>
            <a href="/admin/staff-salaries" class="fi-btn fi-btn-size-md fi-color-gray fi-btn-color-gray fi-ac-action fi-ac-btn-action">Open Staff Salaries</a>
        </div>
    </x-filament::section>

    <x-filament::section heading="Next Product Pass">
        <ul class="list-disc space-y-1 pl-5 text-sm text-slate-700">
            <li>Default allowance and deduction rules by country.</li>
            <li>Company-wide payroll cycles and cut-off dates.</li>
            <li>Approval and lock periods for finalized payroll.</li>
            <li>Advanced tax profile templates linked to policy sets.</li>
        </ul>
    </x-filament::section>
</x-filament-panels::page>
