<x-filament-panels::page>
    <x-filament::section heading="Signature Field Mapper">
        <div class="grid gap-4 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-1">
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="text-sm font-semibold text-slate-900">Context</div>
                    <div class="mt-2 text-xs text-slate-600">Employee: {{ $this->onboarding?->user?->name ?: $this->onboarding?->user_id }}</div>
                    <div class="text-xs text-slate-600">Document: {{ strtoupper($this->documentType) }}</div>
                    <div class="text-xs text-slate-600">Onboarding ID: {{ $this->onboardingId }}</div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Document Path/URL</label>
                    <input type="text" wire:model.live="documentPath" class="w-full rounded-lg border-slate-300 text-sm" placeholder="storage/onboarding-documents/... or URL" />
                    @if (trim((string) $documentPath) !== '')
                        <a href="{{ \App\Support\MediaStorageManager::publicUrl((string) $documentPath, '') }}" target="_blank" rel="noopener" class="mt-2 inline-flex text-xs font-medium text-primary-600 hover:underline">Open source PDF</a>
                    @endif
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="mb-2 flex items-center justify-between">
                        <div class="text-sm font-semibold text-slate-900">Actors</div>
                        <x-filament::button size="xs" color="gray" wire:click="addActor">Add Actor</x-filament::button>
                    </div>

                    <div class="space-y-3">
                        @foreach ($actors as $i => $actor)
                            <div class="rounded-lg border border-slate-200 p-3">
                                <div class="mb-2 flex items-center justify-between">
                                    <div class="text-xs font-semibold text-slate-700">Actor #{{ $i + 1 }}</div>
                                    <button type="button" class="text-xs text-rose-600 hover:underline" wire:click="removeActor({{ $i }})">Remove</button>
                                </div>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    <input type="text" wire:model.live="actors.{{ $i }}.role_label" class="rounded-lg border-slate-300 text-xs" placeholder="Role" />
                                    <input type="number" min="1" wire:model.live="actors.{{ $i }}.sign_order" class="rounded-lg border-slate-300 text-xs" placeholder="Order" />
                                    <input type="text" wire:model.live="actors.{{ $i }}.signer_name" class="rounded-lg border-slate-300 text-xs" placeholder="Signer name" />
                                    <input type="email" wire:model.live="actors.{{ $i }}.signer_email" class="rounded-lg border-slate-300 text-xs" placeholder="Signer email" />
                                    <input type="text" wire:model.live="actors.{{ $i }}.signature_field_key" class="rounded-lg border-slate-300 text-xs" placeholder="Field key" />
                                    <input type="number" min="1" wire:model.live="actors.{{ $i }}.page_number" class="rounded-lg border-slate-300 text-xs" placeholder="Page" />
                                </div>
                                <div class="mt-2 grid gap-2 sm:grid-cols-4">
                                    <input type="number" min="0" wire:model.live="actors.{{ $i }}.position_x" class="rounded-lg border-slate-300 text-xs" placeholder="X" />
                                    <input type="number" min="0" wire:model.live="actors.{{ $i }}.position_y" class="rounded-lg border-slate-300 text-xs" placeholder="Y" />
                                    <input type="number" min="1" wire:model.live="actors.{{ $i }}.field_width" class="rounded-lg border-slate-300 text-xs" placeholder="W" />
                                    <input type="number" min="1" wire:model.live="actors.{{ $i }}.field_height" class="rounded-lg border-slate-300 text-xs" placeholder="H" />
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3">
                        <x-filament::button color="primary" wire:click="saveMappings">Save Mapping</x-filament::button>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="rounded-xl border border-slate-200 bg-white p-4" x-data="signatureMapper(@entangle('actors').live)">
                    <div class="mb-2 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-slate-900">Visual Layout (A4 Canvas)</div>
                            <div class="text-xs text-slate-500">Drag boxes to set X/Y. Use bottom-right handle to resize W/H.</div>
                        </div>
                        <button type="button" class="text-xs text-slate-600 hover:underline" @click="resetToDefaults()">Reset Positions</button>
                    </div>

                    <div class="relative mx-auto h-[920px] w-[650px] overflow-hidden rounded-lg border border-slate-300 bg-white shadow-inner">
                        <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,#f5f7fb_1px,transparent_1px),linear-gradient(to_bottom,#f5f7fb_1px,transparent_1px)] bg-[size:20px_20px]"></div>

                        <template x-for="(actor, index) in actors" :key="index">
                            <div
                                class="absolute select-none rounded-md border-2 border-violet-500 bg-violet-100/70"
                                :style="`left:${num(actor.position_x)}px;top:${num(actor.position_y)}px;width:${num(actor.field_width)}px;height:${num(actor.field_height)}px;`"
                                @mousedown="startDrag($event, index)"
                            >
                                <div class="pointer-events-none truncate bg-violet-600 px-1 py-0.5 text-[10px] font-semibold text-white" x-text="actor.role_label || `Signer ${index+1}`"></div>
                                <div class="pointer-events-none p-1 text-[10px] text-violet-900" x-text="(actor.signature_field_key || 'SIGN') + ' / Pg ' + (actor.page_number || 1)"></div>
                                <div class="absolute bottom-0 right-0 h-3 w-3 cursor-se-resize rounded-tl bg-violet-700" @mousedown.stop="startResize($event, index)"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>

    <script>
        function signatureMapper(actorsEntangled) {
            return {
                actors: actorsEntangled,
                dragIndex: null,
                resizeIndex: null,
                offsetX: 0,
                offsetY: 0,
                startWidth: 0,
                startHeight: 0,
                startMouseX: 0,
                startMouseY: 0,
                num(value) {
                    const n = Number(value);
                    return Number.isFinite(n) ? n : 0;
                },
                startDrag(event, index) {
                    this.dragIndex = index;
                    const actor = this.actors[index] || {};
                    this.offsetX = event.clientX - this.num(actor.position_x);
                    this.offsetY = event.clientY - this.num(actor.position_y);
                    window.addEventListener('mousemove', this.onMouseMove);
                    window.addEventListener('mouseup', this.onMouseUp);
                },
                startResize(event, index) {
                    this.resizeIndex = index;
                    const actor = this.actors[index] || {};
                    this.startWidth = this.num(actor.field_width);
                    this.startHeight = this.num(actor.field_height);
                    this.startMouseX = event.clientX;
                    this.startMouseY = event.clientY;
                    window.addEventListener('mousemove', this.onMouseMove);
                    window.addEventListener('mouseup', this.onMouseUp);
                },
                onMouseMove: null,
                onMouseUp: null,
                resetToDefaults() {
                    this.actors = (this.actors || []).map((actor, i) => ({
                        ...actor,
                        position_x: 60 + (i * 20),
                        position_y: 120 + (i * 20),
                        field_width: 200,
                        field_height: 60,
                    }));
                },
                init() {
                    this.onMouseMove = (event) => {
                        if (this.dragIndex !== null) {
                            const actor = this.actors[this.dragIndex];
                            if (!actor) return;
                            actor.position_x = Math.max(0, Math.round(event.clientX - this.offsetX));
                            actor.position_y = Math.max(0, Math.round(event.clientY - this.offsetY));
                            return;
                        }
                        if (this.resizeIndex !== null) {
                            const actor = this.actors[this.resizeIndex];
                            if (!actor) return;
                            const dx = event.clientX - this.startMouseX;
                            const dy = event.clientY - this.startMouseY;
                            actor.field_width = Math.max(80, Math.round(this.startWidth + dx));
                            actor.field_height = Math.max(40, Math.round(this.startHeight + dy));
                        }
                    };

                    this.onMouseUp = () => {
                        this.dragIndex = null;
                        this.resizeIndex = null;
                        window.removeEventListener('mousemove', this.onMouseMove);
                        window.removeEventListener('mouseup', this.onMouseUp);
                    };
                },
            };
        }
    </script>
</x-filament-panels::page>
