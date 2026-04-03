<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        <x-filament::section heading="Annual Appraisal Structure">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Objectives Weight (%)</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="number" min="0" max="100" wire:model="objective_weight" />
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Values Weight (%)</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="number" min="0" max="100" wire:model="values_weight" />
                    </x-filament::input.wrapper>
                </div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model="annual_section_objectives_enabled" class="rounded border-slate-300">
                    <span class="text-sm">Enable Annual Objectives section</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model="annual_section_values_enabled" class="rounded border-slate-300">
                    <span class="text-sm">Enable Values section</span>
                </label>
            </div>
        </x-filament::section>

        <x-filament::section heading="Cadence & Ownership">
            <div class="grid gap-4 md:grid-cols-2">
                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model="allow_employee_objectives" class="rounded border-slate-300">
                    <span class="text-sm">Allow employees to create objectives</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model="allow_manager_objectives" class="rounded border-slate-300">
                    <span class="text-sm">Allow managers/admin to assign objectives</span>
                </label>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Weekly update due weekday (1=Mon...7=Sun)</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="number" min="1" max="7" wire:model="weekly_update_due_weekday" />
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Monthly update due day</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="number" min="1" max="31" wire:model="monthly_update_due_day" />
                    </x-filament::input.wrapper>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section heading="Annual Workflow Stages">
            <div class="grid gap-4 md:grid-cols-2">
                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model="annual_stage_manager_submit_required" class="rounded border-slate-300">
                    <span class="text-sm">Require manager submission stage</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model="annual_stage_calibration_enabled" class="rounded border-slate-300">
                    <span class="text-sm">Enable calibration stage</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model="annual_stage_joint_review_enabled" class="rounded border-slate-300">
                    <span class="text-sm">Enable joint review stage</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model="annual_stage_employee_ack_required" class="rounded border-slate-300">
                    <span class="text-sm">Require employee acknowledgment</span>
                </label>
                <label class="flex items-center gap-2 md:col-span-2">
                    <input type="checkbox" wire:model="annual_allow_admin_manual_progress" class="rounded border-slate-300">
                    <span class="text-sm">Allow admin manual progression to completion</span>
                </label>
            </div>
        </x-filament::section>

        <x-filament::section heading="Values Catalog">
            <label class="mb-1 block text-sm font-medium text-slate-600">One value per line</label>
            <textarea wire:model="values_catalog_lines" rows="8" class="fi-input block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
        </x-filament::section>

        <div class="flex justify-end">
            <x-filament::button type="submit">Save Performance Settings</x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
