<x-filament-panels::page>
    @php($summary = $this->getSummary())

    <x-filament::section>
        <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-5">
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Annual Reviews</p>
                <p class="text-2xl font-semibold">{{ $summary['total_reviews'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Draft</p>
                <p class="text-2xl font-semibold text-slate-700">{{ $summary['draft'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Self Submitted</p>
                <p class="text-2xl font-semibold text-amber-600">{{ $summary['self_submitted'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Manager Finalized</p>
                <p class="text-2xl font-semibold text-emerald-600">{{ $summary['manager_finalized'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Active Employees</p>
                <p class="text-2xl font-semibold">{{ $summary['active_employees'] }}</p>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section heading="Annual Cycle Controls">
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600">Review Year</label>
                <x-filament::input.wrapper>
                    <x-filament::input type="number" min="2000" max="2100" wire:model.live="year" />
                </x-filament::input.wrapper>
            </div>
            <div class="flex items-end">
                <x-filament::button wire:click="generateAnnualReviews">
                    Generate Annual Reviews For Active Employees
                </x-filament::button>
            </div>
            <div class="flex items-end">
                <a href="{{ route('performance/team/annual-reviews', ['year' => $this->year]) }}" class="fi-btn fi-btn-size-md fi-color-gray fi-btn-color-gray fi-ac-action fi-ac-btn-action">
                    Open Team Annual Appraisals
                </a>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section heading="Performance Links">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <a href="{{ \App\Filament\Pages\PerformanceSettings::getUrl(panel: 'admin') }}" class="rounded-lg border border-slate-200 p-4 hover:border-indigo-300">
                <p class="font-medium">Performance Settings</p>
                <p class="text-sm text-slate-500">Weights, values catalog, section toggles.</p>
            </a>
            <a href="{{ route('performance/team/reviews', ['year' => $this->year]) }}" class="rounded-lg border border-slate-200 p-4 hover:border-indigo-300">
                <p class="font-medium">Weekly/Monthly Team Reviews</p>
                <p class="text-sm text-slate-500">Review submitted period updates.</p>
            </a>
            <a href="{{ route('performance/team/annual-reviews', ['year' => $this->year]) }}" class="rounded-lg border border-slate-200 p-4 hover:border-indigo-300">
                <p class="font-medium">Annual Manager Appraisals</p>
                <p class="text-sm text-slate-500">Finalize annual objective and values ratings.</p>
            </a>
            <a href="{{ route('performance/tracker', ['year' => $this->year]) }}" class="rounded-lg border border-slate-200 p-4 hover:border-indigo-300">
                <p class="font-medium">Employee Tracker View</p>
                <p class="text-sm text-slate-500">Weekly/monthly plans and objectives.</p>
            </a>
        </div>
    </x-filament::section>

    <x-filament::section heading="Legacy Admin Cutover Map">
        @php($cutoverMap = $this->getLegacyCutoverMap())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left">
                    <tr>
                        <th class="px-3 py-2">Legacy Path</th>
                        <th class="px-3 py-2">Filament Path</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($cutoverMap as $legacyPath => $filamentPath)
                        <tr>
                            <td class="px-3 py-2"><code>{{ $legacyPath }}</code></td>
                            <td class="px-3 py-2"><code>{{ $filamentPath }}</code></td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-3 py-3 text-slate-500">No cutover mappings configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
