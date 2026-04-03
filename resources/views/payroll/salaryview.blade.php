@extends('layouts.exportmaster')
@section('content')
    <div class="">
        <div class="page-wrapper">
            <div class="content container-fluid" id="app">
                <div class="page-header">
                    <div class="row align-items-center">
                        <div class="col" style="margin-left: -222px;">
                            <h3 class="page-title">Payslip</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('form/salary/page') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Payslip</li>
                            </ul>
                        </div>
                        <div class="col-auto float-right ml-auto">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-white" style="color: green"><i class="fa fa-file-excel-o"></i><a href="{{ url("extra/report/excel/?user_id=$users->user_id") }}"> Excel</a></button>
                                <button class="btn btn-white" style="color: red"><i class="fa fa-file-pdf-o"></i> <a href="{{ url("extra/report/pdf/?user_id=$users->user_id") }}">PDF</a></button>
                                <button class="btn btn-white" style="color: black"><i class="fa fa-print fa-lg"></i><a href="" @click.prevent="printme" target="_blank"> Print</a></button>
                            </div>
                        </div>
                    </div>

                    @php
                        $avatar = \App\Support\MediaStorageManager::publicUrl($users->avatar ?? null, 'assets/img/profiles/avatar-01.jpg', 'assets/images');
                        $totalEarnings = (int) $users->basic + (int) $users->hra + (int) $users->conveyance + (int) $users->allowance;
                        $totalDeductions = (int) $users->tds + (int) $users->prof_tax + (int) $users->esi + (int) $users->labour_welfare;
                    @endphp

                    <div class="row" style="margin-left: -240px;">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="payslip-title">Payslip for the month of {{ \Carbon\Carbon::now()->format('M') }} {{ \Carbon\Carbon::now()->year }}</h4>
                                    <div class="row">
                                        <div class="col-sm-6 m-b-20">
                                            <img src="{{ $avatar }}" class="inv-logo" alt="{{ $users->name }}">
                                            <ul class="list-unstyled mb-0">
                                                <li>{{ $users->name }}</li>
                                                <li>{{ $users->address ?? 'Address not set' }}</li>
                                                <li>{{ $users->country ?? 'Country not set' }}</li>
                                            </ul>
                                        </div>
                                        <div class="col-sm-6 m-b-20">
                                            <div class="invoice-details">
                                                <h3 class="text-uppercase">Payslip #49029</h3>
                                                <ul class="list-unstyled">
                                                    <li>Salary Month: <span>{{ \Carbon\Carbon::now()->format('M') }}, {{ \Carbon\Carbon::now()->year }}</span></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12 m-b-20">
                                            <ul class="list-unstyled">
                                                <li><h5 class="mb-0"><strong>{{ $users->name }}</strong></h5></li>
                                                <li><span>{{ $users->position }}</span></li>
                                                <li>Employee ID: {{ $users->user_id }}</li>
                                                <li>Joining Date: {{ $users->join_date }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div>
                                                <h4 class="m-b-10"><strong>Earnings</strong></h4>
                                                <table class="table table-bordered">
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>Basic Salary</strong> <span class="float-right">${{ $users->basic }}</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>House Rent Allowance (H.R.A.)</strong> <span class="float-right">${{ $users->hra }}</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Conveyance</strong> <span class="float-right">${{ $users->conveyance }}</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Other Allowance</strong> <span class="float-right">${{ $users->allowance }}</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Total Earnings</strong> <span class="float-right"><strong>${{ $totalEarnings }}</strong></span></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div>
                                                <h4 class="m-b-10"><strong>Deductions</strong></h4>
                                                <table class="table table-bordered">
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>Tax Deducted at Source (T.D.S.)</strong> <span class="float-right">${{ $users->tds }}</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Provident Fund</strong> <span class="float-right">${{ $users->prof_tax }}</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>ESI</strong> <span class="float-right">${{ $users->esi }}</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Loan</strong> <span class="float-right">${{ $users->labour_welfare }}</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Total Deductions</strong> <span class="float-right"><strong>${{ $totalDeductions }}</strong></span></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <p><strong>Net Salary: ${{ $users->salary }}</strong></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
