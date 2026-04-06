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

        <x-filament::section heading="Values Catalog" description="Define the culture values used in annual appraisal scoring.">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 font-semibold text-slate-700">
                        {{ $values_catalog_count }} values detected
                    </span>
                    @if($values_catalog_has_duplicates)
                        <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 font-semibold text-rose-700">
                            Duplicate values found
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700">
                            No duplicates
                        </span>
                    @endif
                </div>

                <p class="text-xs text-slate-500">Add values as structured items with a short code, title, and description. Duplicate titles are blocked at save.</p>

                <div class="space-y-3">
                    @foreach($values_catalog_items as $index => $item)
                        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3">
                            <div class="grid gap-3 md:grid-cols-12">
                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Code</label>
                                    <x-filament::input.wrapper>
                                        <x-filament::input type="text" maxlength="8" wire:model.live.debounce.300ms="values_catalog_items.{{ $index }}.code" placeholder="P" />
                                    </x-filament::input.wrapper>
                                </div>
                                <div class="md:col-span-4">
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Title</label>
                                    <x-filament::input.wrapper>
                                        <x-filament::input type="text" maxlength="80" wire:model.live.debounce.300ms="values_catalog_items.{{ $index }}.title" placeholder="Passionate" />
                                    </x-filament::input.wrapper>
                                </div>
                                <div class="md:col-span-6">
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Description</label>
                                    <textarea
                                        rows="3"
                                        class="fi-input block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                                        wire:model.live.debounce.400ms="values_catalog_items.{{ $index }}.description"
                                        placeholder="What this value means in day-to-day behavior."
                                    ></textarea>
                                </div>
                            </div>
                            <div class="mt-2 flex items-center justify-end">
                                <x-filament::button type="button" color="danger" size="sm" wire:click="removeCatalogValue({{ $index }})">
                                    Remove
                                </x-filament::button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-filament::button type="button" color="gray" wire:click="addCatalogValue">
                        + Add value
                    </x-filament::button>
                    <x-filament::button type="button" color="gray" wire:click="resetValuesCatalogToDefault">
                        Reset To P.U.R.P.L.E Defaults
                    </x-filament::button>
                </div>

                @if($values_catalog_has_duplicates)
                    <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                        Duplicate entries: {{ implode(', ', $values_catalog_duplicates) }}
                    </div>
                @endif

                @if(!empty($values_catalog_preview))
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Preview</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($values_catalog_preview as $value)
                                <span class="inline-flex items-center rounded-full border border-violet-200 bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700">
                                    {{ $value['label'] ?? $value['title'] ?? '' }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </x-filament::section>

        <div class="sticky bottom-4 z-10 flex flex-wrap items-center justify-end gap-3 rounded-xl border border-slate-200 bg-white/95 px-4 py-3 shadow-sm backdrop-blur">
            @if($last_saved_at)
                <span class="mr-auto text-xs text-slate-500">Last saved: {{ $last_saved_at }}</span>
            @endif
            <x-filament::button type="submit">Save Performance Settings</x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
