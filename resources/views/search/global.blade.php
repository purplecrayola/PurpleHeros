@extends('layouts.master')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Search Results</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ Auth::user()->isAdmin() ? route('home') : route('em/dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Search</li>
                        </ul>
                        <p class="text-muted mb-0">Results for "<strong>{{ $query }}</strong>"</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Quick Navigation</h5>
                    @if($shortcuts->isNotEmpty())
                        <div class="d-flex flex-wrap" style="gap: 10px;">
                            @foreach($shortcuts as $shortcut)
                                <a href="{{ $shortcut['url'] }}" class="btn btn-outline-primary">{{ $shortcut['label'] }}</a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No matching quick links.</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ $isAdmin ? 'People' : 'People (Your Access)' }}</h5>
                    @if($people->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-striped custom-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>User ID</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th class="text-right">Open</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($people as $person)
                                        <tr>
                                            <td>{{ $person->name }}</td>
                                            <td>{{ $person->user_id }}</td>
                                            <td>{{ $person->email }}</td>
                                            <td>{{ $person->role_name ?? 'User' }}</td>
                                            <td class="text-right">
                                                <a href="{{ url('employee/profile/' . $person->user_id) }}" class="btn btn-sm btn-primary">Profile</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No people matched this search.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
