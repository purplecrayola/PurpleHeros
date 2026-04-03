@extends('layouts.settings')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="row">
                <div class="col-md-10 offset-md-1">
                    <div class="page-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="page-title">Email Delivery</h3>
                                <p class="text-muted mb-0">This page documents the email delivery capability expected for customer deployments. SMTP persistence is not wired into the application yet.</p>
                            </div>
                        </div>
                    </div>
                    @include('settings.partials.settings-tabs', ['active' => 'email'])

                    {!! Toastr::message() !!}

                    <div class="card mb-4">
                        <div class="card-body">
                            <form action="{{ route('email/settings/save') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Mailer</label>
                                            <select name="mail_mailer" class="form-control @error('mail_mailer') is-invalid @enderror">
                                                @php($selectedMailer = old('mail_mailer', $companySettings->mail_mailer ?? 'log'))
                                                <option value="log" {{ $selectedMailer === 'log' ? 'selected' : '' }}>Log (No external delivery)</option>
                                                <option value="smtp" {{ $selectedMailer === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                                <option value="ses" {{ $selectedMailer === 'ses' ? 'selected' : '' }}>AWS SES</option>
                                            </select>
                                            @error('mail_mailer')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>AWS SES Enabled</label>
                                            <select name="ses_enabled" class="form-control @error('ses_enabled') is-invalid @enderror">
                                                @php($sesEnabled = (int) old('ses_enabled', (int) ($companySettings->ses_enabled ?? 0)))
                                                <option value="0" {{ $sesEnabled === 0 ? 'selected' : '' }}>No</option>
                                                <option value="1" {{ $sesEnabled === 1 ? 'selected' : '' }}>Yes</option>
                                            </select>
                                            @error('ses_enabled')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>SES Region</label>
                                            <input type="text" name="ses_region" class="form-control @error('ses_region') is-invalid @enderror" value="{{ old('ses_region', $companySettings->ses_region ?? 'us-east-1') }}" placeholder="us-east-1">
                                            @error('ses_region')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>SES Access Key ID</label>
                                            <input type="text" name="ses_access_key_id" class="form-control @error('ses_access_key_id') is-invalid @enderror" value="{{ old('ses_access_key_id', $companySettings->ses_access_key_id) }}" placeholder="AKIA...">
                                            @error('ses_access_key_id')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>SES Secret Access Key</label>
                                            <input type="password" name="ses_secret_access_key" class="form-control @error('ses_secret_access_key') is-invalid @enderror" value="{{ old('ses_secret_access_key', $companySettings->ses_secret_access_key) }}" placeholder="Secret key">
                                            @error('ses_secret_access_key')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>SES Configuration Set (Optional)</label>
                                            <input type="text" name="ses_configuration_set" class="form-control @error('ses_configuration_set') is-invalid @enderror" value="{{ old('ses_configuration_set', $companySettings->ses_configuration_set) }}" placeholder="transactional-mails">
                                            @error('ses_configuration_set')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6"></div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>From Email</label>
                                            <input type="email" name="mail_from_address" class="form-control @error('mail_from_address') is-invalid @enderror" value="{{ old('mail_from_address', $companySettings->mail_from_address ?: $companySettings->email) }}" placeholder="noreply@purplecrayola.com">
                                            @error('mail_from_address')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>From Name</label>
                                            <input type="text" name="mail_from_name" class="form-control @error('mail_from_name') is-invalid @enderror" value="{{ old('mail_from_name', $companySettings->mail_from_name ?: $companySettings->company_name) }}" placeholder="Purple Crayola">
                                            @error('mail_from_name')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Reply-To Email (Optional)</label>
                                            <input type="email" name="mail_reply_to_address" class="form-control @error('mail_reply_to_address') is-invalid @enderror" value="{{ old('mail_reply_to_address', $companySettings->mail_reply_to_address) }}" placeholder="hr@purplecrayola.com">
                                            @error('mail_reply_to_address')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="submit-section mb-0">
                                    <button type="submit" class="btn btn-primary submit-btn">Save Email Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">Send Test Email</h5>
                            <p class="text-muted mb-3">After saving, send a test message to confirm delivery and sender configuration.</p>
                            <form action="{{ route('email/settings/test') }}" method="POST" class="row">
                                @csrf
                                <div class="col-md-8">
                                    <div class="form-group mb-md-0">
                                        <input type="email" name="test_recipient" class="form-control @error('test_recipient') is-invalid @enderror" value="{{ old('test_recipient', $companySettings->email) }}" placeholder="recipient@example.com">
                                        @error('test_recipient')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-right">
                                    <button type="submit" class="btn btn-outline-primary btn-block">Send Test Email</button>
                                </div>
                            </form>
                            <div class="alert alert-info mt-4 mb-0">
                                SES requires verified identities and proper IAM permissions (`ses:SendEmail` / `ses:SendRawEmail`) for the configured region.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
