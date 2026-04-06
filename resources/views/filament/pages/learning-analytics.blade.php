<x-filament-panels::page>
    @php($summary = $this->getSummary())
    @php($topCourses = $this->getTopCourses())
    @php($assetTypeBreakdown = $this->getAssetTypeBreakdown())
    @php($overdueEnrollments = $this->getOverdueEnrollments())

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-filament::section>
            <div class="text-sm text-gray-500">Courses</div>
            <div class="text-2xl font-semibold">{{ $summary['courses'] }}</div>
            <div class="text-xs text-gray-500">Catalog Visible: {{ $summary['catalog_visible'] }}</div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500">Enrollments</div>
            <div class="text-2xl font-semibold">{{ $summary['enrollments'] }}</div>
            <div class="text-xs text-gray-500">In Progress: {{ $summary['in_progress'] }}</div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500">Completed</div>
            <div class="text-2xl font-semibold">{{ $summary['completed'] }}</div>
            <div class="text-xs text-gray-500">Avg Completion: {{ number_format($summary['avg_completion'], 1) }}%</div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500">Progress Events</div>
            <div class="text-2xl font-semibold">{{ $summary['progress_events'] }}</div>
            <div class="text-xs text-gray-500">Bookmarks and interactions</div>
        </x-filament::section>
    </div>

    <x-filament::section heading="Top Courses By Enrollment">
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="px-2 py-2 text-left">Course</th>
                        <th class="px-2 py-2 text-left">Mode</th>
                        <th class="px-2 py-2 text-left">Enrollments</th>
                        <th class="px-2 py-2 text-left">Avg Completion</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topCourses as $course)
                        <tr class="border-b">
                            <td class="px-2 py-2">{{ $course->title }}</td>
                            <td class="px-2 py-2">{{ ucfirst(str_replace('_', ' ', $course->delivery_mode)) }}</td>
                            <td class="px-2 py-2">{{ (int) $course->enrollments_count }}</td>
                            <td class="px-2 py-2">{{ number_format((float) ($course->avg_completion ?? 0), 1) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-2 py-3 pc-empty-state">No learning data yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section heading="Asset Type Breakdown">
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="px-2 py-2 text-left">Asset Type</th>
                        <th class="px-2 py-2 text-left">Assets</th>
                        <th class="px-2 py-2 text-left">Events</th>
                        <th class="px-2 py-2 text-left">Avg Progress Event %</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assetTypeBreakdown as $row)
                        <tr class="border-b">
                            <td class="px-2 py-2">{{ strtoupper((string) $row['asset_type']) }}</td>
                            <td class="px-2 py-2">{{ (int) $row['assets_count'] }}</td>
                            <td class="px-2 py-2">{{ (int) $row['events_count'] }}</td>
                            <td class="px-2 py-2">{{ number_format((float) $row['avg_completion'], 1) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-2 py-3 pc-empty-state">No asset telemetry yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section heading="Overdue Enrollment Queue">
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="px-2 py-2 text-left">Employee</th>
                        <th class="px-2 py-2 text-left">Course</th>
                        <th class="px-2 py-2 text-left">Due Date</th>
                        <th class="px-2 py-2 text-left">Status</th>
                        <th class="px-2 py-2 text-left">Completion</th>
                        <th class="px-2 py-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overdueEnrollments as $enrollment)
                        <tr class="border-b">
                            <td class="px-2 py-2">{{ $enrollment->user->name ?? 'Unknown User' }}</td>
                            <td class="px-2 py-2">{{ $enrollment->course->title ?? 'Unknown Course' }}</td>
                            <td class="px-2 py-2">{{ optional($enrollment->due_at)->format('d M Y') }}</td>
                            <td class="px-2 py-2">{{ ucwords(str_replace('_', ' ', (string) $enrollment->status)) }}</td>
                            <td class="px-2 py-2">{{ number_format((float) $enrollment->completion_percent, 1) }}%</td>
                            <td class="px-2 py-2">
                                <a
                                    href="{{ route('filament.admin.resources.learning-enrollments.edit', ['record' => $enrollment->id]) }}"
                                    class="fi-btn fi-btn-size-xs fi-color-gray fi-btn-color-gray fi-ac-action fi-ac-btn-action"
                                >
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-2 py-3 pc-empty-state">No overdue enrollments.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
