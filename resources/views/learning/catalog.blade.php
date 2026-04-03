@extends('layouts.master')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Learning Catalog</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('em/dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Learning Catalog</li>
                        </ul>
                    </div>
                </div>
            </div>

            {!! Toastr::message() !!}

            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('learning/catalog') }}" class="row">
                        <div class="col-md-10">
                            <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Search courses, topics, or course code">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block">Search</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                @forelse($courses as $course)
                    @php($enrollment = $myEnrollments[$course->id] ?? null)
                    <div class="col-md-6 col-xl-4 d-flex">
                        <div class="card flex-fill">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h4 class="mb-0">{{ $course->title }}</h4>
                                    <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $course->delivery_mode)) }}</span>
                                </div>
                                <div class="text-muted mb-2">
                                    {{ $course->course_code ?: 'No code' }} · {{ $course->assets_count }} assets
                                </div>
                                <p class="text-muted mb-3">{{ \Illuminate\Support\Str::limit((string) $course->description, 110) ?: 'No description provided yet.' }}</p>

                                @if($enrollment)
                                    <p class="mb-1"><strong>Status:</strong> {{ ucwords(str_replace('_', ' ', $enrollment->status)) }}</p>
                                    <p class="mb-3"><strong>Completion:</strong> {{ number_format((float) $enrollment->completion_percent, 1) }}%</p>
                                    <form method="POST" action="{{ route('learning/course/start', ['id' => $course->id]) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">Start</button>
                                    </form>
                                    <a href="{{ route('learning/course/view', ['id' => $course->id]) }}" class="btn btn-outline-secondary btn-sm">Open</a>
                                @else
                                    <span class="badge badge-warning">Not enrolled</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-light border">No catalog courses are visible right now.</div>
                    </div>
                @endforelse
            </div>

            <div class="mt-2">
                {{ $courses->links() }}
            </div>
        </div>
    </div>
@endsection

