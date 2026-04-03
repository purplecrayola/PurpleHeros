<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Annual Appraisal</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        .header { margin-bottom: 16px; }
        .title { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
        .meta { color: #4b5563; margin-bottom: 2px; }
        .section-title { font-size: 14px; font-weight: 700; margin: 18px 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
        .summary { border: 1px solid #d1d5db; padding: 8px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Annual Performance Appraisal</div>
        <div class="meta">Employee: {{ $subjectUser->name }} ({{ $subjectUser->user_id }})</div>
        <div class="meta">Year: {{ $review->review_year }}</div>
        <div class="meta">Status: {{ ucfirst(str_replace('_', ' ', $review->status)) }}</div>
        <div class="meta">Final Manager Score: {{ $review->manager_final_score ?? '-' }}</div>
    </div>

    <div class="section-title">Monthly Goals Rollup</div>
    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th>Goal</th>
                <th>Employee Update</th>
                <th>Completion</th>
                <th>Manager Comment</th>
            </tr>
        </thead>
        <tbody>
            @forelse($monthlyGoals as $goal)
                <tr>
                    <td>{{ $goal->period_number }}</td>
                    <td>{{ $goal->title }}<br><small>{{ $goal->planned_tasks }}</small></td>
                    <td>{{ $goal->end_period_update ?: '-' }}</td>
                    <td>{{ $goal->completion_percent !== null ? $goal->completion_percent . '%' : '-' }}</td>
                    <td>{{ $goal->manager_comment ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No monthly goals found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Section 1: Annual Objectives</div>
    <table>
        <thead>
            <tr>
                <th>Objective</th>
                <th>Weight</th>
                <th>Self Rating</th>
                <th>Manager Rating</th>
                <th>Manager Comment</th>
            </tr>
        </thead>
        <tbody>
            @forelse($objectiveRatings as $rating)
                <tr>
                    <td>{{ $rating->objective?->title ?? 'Objective removed' }}</td>
                    <td>{{ $rating->objective && $rating->objective->weight !== null ? number_format((float) $rating->objective->weight, 1) . '%' : '-' }}</td>
                    <td>{{ $rating->self_rating ?? '-' }}</td>
                    <td>{{ $rating->manager_rating ?? '-' }}</td>
                    <td>{{ $rating->manager_comment ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No objective ratings found.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($settings->annual_section_values_enabled)
        <div class="section-title">Section 2: Values</div>
        <table>
            <thead>
                <tr>
                    <th>Value</th>
                    <th>Self Rating</th>
                    <th>Manager Rating</th>
                    <th>Manager Comment</th>
                </tr>
            </thead>
            <tbody>
                @foreach($valueRatings as $rating)
                    <tr>
                        <td>{{ $rating->value_label }}</td>
                        <td>{{ $rating->self_rating ?? '-' }}</td>
                        <td>{{ $rating->manager_rating ?? '-' }}</td>
                        <td>{{ $rating->manager_comment ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="section-title">Self Summary</div>
    <div class="summary">{{ $review->self_summary ?: '-' }}</div>

    <div class="section-title">Manager Summary</div>
    <div class="summary">{{ $review->manager_summary ?: '-' }}</div>
</body>
</html>
