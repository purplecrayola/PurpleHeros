@extends('layouts.master')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Team Performance Reviews</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Team Reviews</li>
                        </ul>
                    </div>
                </div>
            </div>

            {!! Toastr::message() !!}

            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('performance/team/reviews') }}" method="GET" class="row">
                        <div class="col-md-3">
                            <label>Year</label>
                            <select class="form-control" name="year">
                                @foreach($years as $availableYear)
                                    <option value="{{ $availableYear }}" {{ (int) $year === (int) $availableYear ? 'selected' : '' }}>{{ $availableYear }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Status</label>
                            <select class="form-control" name="status">
                                @foreach(['draft' => 'Draft', 'submitted' => 'Submitted', 'reviewed' => 'Reviewed'] as $statusValue => $statusLabel)
                                    <option value="{{ $statusValue }}" {{ $status === $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 align-self-end">
                            <button type="submit" class="btn btn-success">Apply</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table mb-0">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Period</th>
                                    <th>Goal</th>
                                    <th>Employee Update</th>
                                    <th>Status</th>
                                    <th>Manager Review</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entries as $entry)
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold">{{ $entry->employee_name }}</div>
                                            <div class="text-muted">{{ $entry->department ?: '-' }} · {{ $entry->position ?: '-' }}</div>
                                        </td>
                                        <td>{{ ucfirst($entry->period_type) }}</td>
                                        <td>{{ $entry->period_year }} / {{ $entry->period_number }}</td>
                                        <td>
                                            <div class="font-weight-bold">{{ $entry->title }}</div>
                                            <div class="text-muted">{{ $entry->planned_tasks }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $entry->end_period_update ?: '-' }}</div>
                                            <div class="text-muted">Completion: {{ $entry->completion_percent !== null ? $entry->completion_percent . '%' : '-' }}</div>
                                            <div class="text-muted">Blockers: {{ $entry->blockers ?: '-' }}</div>
                                        </td>
                                        <td>{{ ucfirst($entry->status) }}</td>
                                        <td style="min-width: 280px;">
                                            <form action="{{ route('performance/team/reviews/save', $entry->id) }}" method="POST">
                                                @csrf
                                                <div class="form-group mb-1">
                                                    <textarea class="form-control" name="manager_comment" rows="2" required>{{ $entry->manager_comment }}</textarea>
                                                </div>
                                                <div class="form-group mb-1">
                                                    <select class="form-control" name="status" required>
                                                        <option value="submitted" {{ $entry->status === 'submitted' ? 'selected' : '' }}>Keep as Submitted</option>
                                                        <option value="reviewed" {{ $entry->status === 'reviewed' ? 'selected' : '' }}>Mark Reviewed</option>
                                                    </select>
                                                </div>
                                                <button class="btn btn-sm btn-primary">Save Review</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted">No entries found for selected filters.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
