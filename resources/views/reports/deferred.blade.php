@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">{{ $title }}</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">{{ $title }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Deferred Module</h4>
                    <p class="mb-3">{{ $summary }}</p>
                    <p class="text-muted mb-0">This route has been left in place for compatibility, but it is not part of the active Purple HR SMB v1 commercial surface.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
