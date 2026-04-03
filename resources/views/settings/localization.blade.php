@extends('layouts.settings')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="row">
                <div class="col-md-10 offset-md-1">
                    <div class="page-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="page-title">Localization</h3>
                                <p class="text-muted mb-0">Use these defaults as operating guidance for this installation. They are product-facing references and not yet connected to dynamic formatting logic.</p>
                            </div>
                        </div>
                    </div>
                    @include('settings.partials.settings-tabs', ['active' => 'localization'])

                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <label>Recommended Country</label>
                                        <input class="form-control" readonly value="Nigeria" type="text">
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <label>Preferred Date Format</label>
                                        <input class="form-control" readonly value="DD MMM YYYY" type="text">
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <label>Timezone</label>
                                        <input class="form-control" readonly value="Africa/Lagos" type="text">
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <label>Language</label>
                                        <input class="form-control" readonly value="English" type="text">
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <label>Currency Code</label>
                                        <input class="form-control" readonly value="NGN" type="text">
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <label>Currency Symbol</label>
                                        <input class="form-control" readonly value="₦" type="text">
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info mb-0">
                                Persisted localization controls will be added when date, time, payroll, and reporting outputs are standardized across the product.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
