@extends('layouts.settings')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="row">
                <div class="col-md-10 offset-md-1">
                    <div class="page-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="page-title">Payroll Defaults</h3>
                                <p class="text-muted mb-0">This edition supports salary records and payslips. Policy-driven payroll rules still need a dedicated configuration model.</p>
                            </div>
                        </div>
                    </div>
                    @include('settings.partials.settings-tabs', ['active' => 'payroll'])

                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 col-lg-3">
                                    <div class="form-group mb-0">
                                        <label class="text-muted mb-1">DA / HRA Rules</label>
                                        <div class="h5 mb-0">Manual</div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="form-group mb-0">
                                        <label class="text-muted mb-1">Payslip Output</label>
                                        <div class="h5 mb-0">Enabled</div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="form-group mb-0">
                                        <label class="text-muted mb-1">Payroll Engine</label>
                                        <div class="h5 mb-0">Basic</div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="form-group mb-0">
                                        <label class="text-muted mb-1">Country Fit</label>
                                        <div class="h5 mb-0">Single-country</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">Current Product Scope</h5>
                            <ul>
                                <li>Net salary, earnings, and deduction components are stored per employee salary record</li>
                                <li>Payslips can be generated from salary records</li>
                                <li>Payroll policy settings are not centralized yet</li>
                            </ul>

                            <h5 class="mb-3 mt-4">Next Product Pass</h5>
                            <ul class="mb-0">
                                <li>Default allowance and deduction rules</li>
                                <li>Country-specific payroll components</li>
                                <li>Company-wide payroll cycles and cut-off dates</li>
                                <li>Approval and lock periods for finalized payroll</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
