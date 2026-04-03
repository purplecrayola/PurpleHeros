@extends('layouts.settings')
@section('content')
    {!! Toastr::message() !!}

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="row">
                <div class="col-md-10 offset-md-1">
                    <div class="page-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="page-title">Company Profile</h3>
                                <p class="text-muted mb-0">Set the core business identity, brand assets, and Purple Crayola color system used across login and the app shell.</p>
                            </div>
                        </div>
                    </div>
                    @include('settings.partials.settings-tabs', ['active' => 'company'])

                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="text-muted mb-1">Primary Brand</label>
                                        <div class="h5 mb-0">{{ $companySettings->company_name ?: 'Not configured yet' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="text-muted mb-1">Primary Contact</label>
                                        <div class="h5 mb-0">{{ $companySettings->contact_person ?: 'Not configured yet' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="text-muted mb-1">Business Email</label>
                                        <div class="h5 mb-0">{{ $companySettings->email ?: 'Not configured yet' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('company/settings/save') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="section-header mb-4">
                                    <h4 class="mb-1">Business Identity</h4>
                                    <p class="text-muted mb-0">Core company details used in employee and payroll workflows.</p>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Company Name <span class="text-danger">*</span></label>
                                            <input class="form-control @error('company_name') is-invalid @enderror" type="text" name="company_name" value="{{ old('company_name', $companySettings->company_name) }}" placeholder="Purple Crayola HR">
                                            @error('company_name')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Contact Person <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('contact_person') is-invalid @enderror" name="contact_person" value="{{ old('contact_person', $companySettings->contact_person) }}" placeholder="HR or operations lead">
                                            @error('contact_person')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label>Business Address <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('address') is-invalid @enderror" name="address" value="{{ old('address', $companySettings->address) }}" placeholder="Street address, building, district">
                                            @error('address')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6 col-md-6 col-lg-3">
                                        <div class="form-group">
                                            <label>Country <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('country') is-invalid @enderror" name="country" value="{{ old('country', $companySettings->country) }}" placeholder="Nigeria">
                                            @error('country')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-3">
                                        <div class="form-group">
                                            <label>City <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city', $companySettings->city) }}" placeholder="Lagos">
                                            @error('city')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-3">
                                        <div class="form-group">
                                            <label>State / Province <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('state_province') is-invalid @enderror" name="state_province" value="{{ old('state_province', $companySettings->state_province) }}" placeholder="Lagos State">
                                            @error('state_province')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-3">
                                        <div class="form-group">
                                            <label>Postal Code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('postal_code') is-invalid @enderror" name="postal_code" value="{{ old('postal_code', $companySettings->postal_code) }}" placeholder="100001">
                                            @error('postal_code')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $companySettings->email) }}" placeholder="hello@company.com">
                                            @error('email')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Phone Number <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" name="phone_number" value="{{ old('phone_number', $companySettings->phone_number) }}" placeholder="Main company line">
                                            @error('phone_number')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>Mobile Number</label>
                                            <input type="tel" class="form-control @error('mobile_number') is-invalid @enderror" name="mobile_number" value="{{ old('mobile_number', $companySettings->mobile_number) }}" placeholder="Optional">
                                            @error('mobile_number')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>Fax</label>
                                            <input type="text" class="form-control @error('fax') is-invalid @enderror" name="fax" value="{{ old('fax', $companySettings->fax) }}" placeholder="Optional">
                                            @error('fax')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>Website URL</label>
                                            <input type="url" class="form-control @error('website_url') is-invalid @enderror" name="website_url" value="{{ old('website_url', $companySettings->website_url) }}" placeholder="https://example.com">
                                            @error('website_url')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="section-header mb-4">
                                    <h4 class="mb-1">Branding</h4>
                                    <p class="text-muted mb-0">Control the app logo, login visual, and the default Purple Crayola palette without editing code.</p>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-lg-3">
                                        <div class="brand-settings-preview-card">
                                            <span class="brand-settings-preview-label">Header Logo</span>
                                            <div class="brand-settings-logo brand-settings-logo-dark">
                                                <img src="{{ $companySettings->assetUrl('header_logo_path', 'assets/img/brand/purplecrayola-white.svg') }}" alt="Header logo preview">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="brand-settings-preview-card">
                                            <span class="brand-settings-preview-label">Login Logo</span>
                                            <div class="brand-settings-logo brand-settings-logo-light">
                                                <img src="{{ $companySettings->assetUrl('login_logo_path', 'assets/img/brand/purplecrayola-black.svg') }}" alt="Login logo preview">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="brand-settings-preview-card">
                                            <span class="brand-settings-preview-label">Favicon</span>
                                            <div class="brand-settings-logo brand-settings-logo-light">
                                                <img src="{{ $companySettings->assetUrl('favicon_path', 'assets/img/favicon.ico') }}" alt="Favicon preview" style="max-width:64px;max-height:64px;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="brand-settings-preview-card">
                                            <span class="brand-settings-preview-label">Login Image</span>
                                            <div class="brand-settings-image-preview" style="background-image:url('{{ $companySettings->assetUrl('login_image_path', 'assets/img/brand/login-background.jpg') }}')"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>Upload Header Logo</label>
                                            <input type="file" class="form-control @error('header_logo_file') is-invalid @enderror" name="header_logo_file" accept="image/*">
                                            <small class="form-text text-muted">Upload a replacement for the app header logo.</small>
                                            @error('header_logo_file')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                        <div class="form-group">
                                            <label>Header Logo Path</label>
                                            <input type="text" class="form-control @error('header_logo_path') is-invalid @enderror" name="header_logo_path" value="{{ old('header_logo_path', $companySettings->header_logo_path) }}" placeholder="assets/img/brand/purplecrayola-white.svg">
                                            <small class="form-text text-muted">Used in the top app header and settings shell.</small>
                                            @error('header_logo_path')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>Upload Login Logo</label>
                                            <input type="file" class="form-control @error('login_logo_file') is-invalid @enderror" name="login_logo_file" accept="image/*">
                                            <small class="form-text text-muted">Upload the mark shown above the sign-in form.</small>
                                            @error('login_logo_file')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                        <div class="form-group">
                                            <label>Login Logo Path</label>
                                            <input type="text" class="form-control @error('login_logo_path') is-invalid @enderror" name="login_logo_path" value="{{ old('login_logo_path', $companySettings->login_logo_path) }}" placeholder="assets/img/brand/purplecrayola-black.svg">
                                            <small class="form-text text-muted">Shown above the sign-in form.</small>
                                            @error('login_logo_path')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>Upload Favicon</label>
                                            <input type="file" class="form-control @error('favicon_file') is-invalid @enderror" name="favicon_file" accept=".ico,image/png,image/jpeg,image/webp">
                                            <small class="form-text text-muted">Shown in browser tabs and bookmarks.</small>
                                            @error('favicon_file')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                        <div class="form-group">
                                            <label>Favicon Path</label>
                                            <input type="text" class="form-control @error('favicon_path') is-invalid @enderror" name="favicon_path" value="{{ old('favicon_path', $companySettings->favicon_path) }}" placeholder="assets/img/favicon.ico">
                                            <small class="form-text text-muted">Supports .ico, .png, .jpg, and .webp files.</small>
                                            @error('favicon_path')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>Upload Login Image</label>
                                            <input type="file" class="form-control @error('login_image_file') is-invalid @enderror" name="login_image_file" accept="image/*">
                                            <small class="form-text text-muted">Upload a full visual for the login hero panel.</small>
                                            @error('login_image_file')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                        <div class="form-group">
                                            <label>Login Image Path</label>
                                            <input type="text" class="form-control @error('login_image_path') is-invalid @enderror" name="login_image_path" value="{{ old('login_image_path', $companySettings->login_image_path) }}" placeholder="assets/img/brand/login-background.jpg">
                                            <small class="form-text text-muted">Used as the visual background on the login page.</small>
                                            @error('login_image_path')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Primary Brand Color</label>
                                            <div class="brand-color-field">
                                                <span class="brand-color-swatch" style="background: {{ old('brand_primary_color', $companySettings->brand_primary_color ?: '#8000F9') }}"></span>
                                                <input type="text" class="form-control @error('brand_primary_color') is-invalid @enderror" name="brand_primary_color" value="{{ old('brand_primary_color', $companySettings->brand_primary_color ?: '#8000F9') }}" placeholder="#8000F9">
                                            </div>
                                            @error('brand_primary_color')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Dark Shell Color</label>
                                            <div class="brand-color-field">
                                                <span class="brand-color-swatch" style="background: {{ old('brand_dark_color', $companySettings->brand_dark_color ?: '#021530') }}"></span>
                                                <input type="text" class="form-control @error('brand_dark_color') is-invalid @enderror" name="brand_dark_color" value="{{ old('brand_dark_color', $companySettings->brand_dark_color ?: '#021530') }}" placeholder="#021530">
                                            </div>
                                            @error('brand_dark_color')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Neutral Surface Color</label>
                                            <div class="brand-color-field">
                                                <span class="brand-color-swatch" style="background: {{ old('brand_neutral_color', $companySettings->brand_neutral_color ?: '#DCDDDF') }}"></span>
                                                <input type="text" class="form-control @error('brand_neutral_color') is-invalid @enderror" name="brand_neutral_color" value="{{ old('brand_neutral_color', $companySettings->brand_neutral_color ?: '#DCDDDF') }}" placeholder="#DCDDDF">
                                            </div>
                                            @error('brand_neutral_color')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Header Text Color</label>
                                            <div class="brand-color-field">
                                                <span class="brand-color-swatch" style="background: {{ old('header_text_color', $companySettings->header_text_color ?: '#FFFFFF') }}"></span>
                                                <input type="text" class="form-control @error('header_text_color') is-invalid @enderror" name="header_text_color" value="{{ old('header_text_color', $companySettings->header_text_color ?: '#FFFFFF') }}" placeholder="#FFFFFF">
                                            </div>
                                            @error('header_text_color')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Sidebar Text Color</label>
                                            <div class="brand-color-field">
                                                <span class="brand-color-swatch" style="background: {{ old('sidebar_text_color', $companySettings->sidebar_text_color ?: '#F5F7FF') }}"></span>
                                                <input type="text" class="form-control @error('sidebar_text_color') is-invalid @enderror" name="sidebar_text_color" value="{{ old('sidebar_text_color', $companySettings->sidebar_text_color ?: '#F5F7FF') }}" placeholder="#F5F7FF">
                                            </div>
                                            @error('sidebar_text_color')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Sidebar Muted Text Color</label>
                                            <div class="brand-color-field">
                                                <span class="brand-color-swatch" style="background: {{ old('sidebar_muted_text_color', $companySettings->sidebar_muted_text_color ?: '#A9B8CC') }}"></span>
                                                <input type="text" class="form-control @error('sidebar_muted_text_color') is-invalid @enderror" name="sidebar_muted_text_color" value="{{ old('sidebar_muted_text_color', $companySettings->sidebar_muted_text_color ?: '#A9B8CC') }}" placeholder="#A9B8CC">
                                            </div>
                                            @error('sidebar_muted_text_color')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Workflow Current Step Color</label>
                                            <div class="brand-color-field">
                                                <span class="brand-color-swatch" style="background: {{ old('workflow_current_color', $companySettings->workflow_current_color ?: '#8A00FF') }}"></span>
                                                <input type="text" class="form-control @error('workflow_current_color') is-invalid @enderror" name="workflow_current_color" value="{{ old('workflow_current_color', $companySettings->workflow_current_color ?: '#8A00FF') }}" placeholder="#8A00FF">
                                            </div>
                                            @error('workflow_current_color')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Workflow Completed Step Color</label>
                                            <div class="brand-color-field">
                                                <span class="brand-color-swatch" style="background: {{ old('workflow_completed_color', $companySettings->workflow_completed_color ?: '#0F9D58') }}"></span>
                                                <input type="text" class="form-control @error('workflow_completed_color') is-invalid @enderror" name="workflow_completed_color" value="{{ old('workflow_completed_color', $companySettings->workflow_completed_color ?: '#0F9D58') }}" placeholder="#0F9D58">
                                            </div>
                                            @error('workflow_completed_color')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Workflow Pending Step Color</label>
                                            <div class="brand-color-field">
                                                <span class="brand-color-swatch" style="background: {{ old('workflow_pending_color', $companySettings->workflow_pending_color ?: '#DCDDDF') }}"></span>
                                                <input type="text" class="form-control @error('workflow_pending_color') is-invalid @enderror" name="workflow_pending_color" value="{{ old('workflow_pending_color', $companySettings->workflow_pending_color ?: '#DCDDDF') }}" placeholder="#DCDDDF">
                                            </div>
                                            @error('workflow_pending_color')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="section-header mb-4">
                                    <h4 class="mb-1">Login Experience</h4>
                                    <p class="text-muted mb-0">Control the immersive login title, side notes, and helper lines shown around the sign-in card.</p>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Page Title</label>
                                            <input type="text" class="form-control @error('login_page_title') is-invalid @enderror" name="login_page_title" value="{{ old('login_page_title', $companySettings->login_page_title) }}" placeholder="PurpleHeros Access">
                                            @error('login_page_title')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Brand Label</label>
                                            <input type="text" class="form-control @error('login_brand_label') is-invalid @enderror" name="login_brand_label" value="{{ old('login_brand_label', $companySettings->login_brand_label) }}" placeholder="PurpleHeros">
                                            @error('login_brand_label')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Hero Title</label>
                                            <input type="text" class="form-control @error('login_hero_title') is-invalid @enderror" name="login_hero_title" value="{{ old('login_hero_title', $companySettings->login_hero_title) }}" placeholder="Sign in to PurpleHeros">
                                            @error('login_hero_title')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Hero Copy</label>
                                            <textarea class="form-control @error('login_hero_copy') is-invalid @enderror" name="login_hero_copy" rows="3" placeholder="Short login support copy">{{ old('login_hero_copy', $companySettings->login_hero_copy) }}</textarea>
                                            @error('login_hero_copy')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Left Note Label</label>
                                            <input type="text" class="form-control @error('login_left_label') is-invalid @enderror" name="login_left_label" value="{{ old('login_left_label', $companySettings->login_left_label) }}" placeholder="PurpleHeros">
                                            @error('login_left_label')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                        <div class="form-group">
                                            <label>Left Note Copy</label>
                                            <textarea class="form-control @error('login_left_copy') is-invalid @enderror" name="login_left_copy" rows="4" placeholder="Left side support copy">{{ old('login_left_copy', $companySettings->login_left_copy) }}</textarea>
                                            @error('login_left_copy')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Right Note Label</label>
                                            <input type="text" class="form-control @error('login_right_label') is-invalid @enderror" name="login_right_label" value="{{ old('login_right_label', $companySettings->login_right_label) }}" placeholder="Access">
                                            @error('login_right_label')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                        <div class="form-group">
                                            <label>Right Note Copy</label>
                                            <textarea class="form-control @error('login_right_copy') is-invalid @enderror" name="login_right_copy" rows="4" placeholder="Right side support copy">{{ old('login_right_copy', $companySettings->login_right_copy) }}</textarea>
                                            @error('login_right_copy')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Helper Line One</label>
                                            <input type="text" class="form-control @error('login_help_line_one') is-invalid @enderror" name="login_help_line_one" value="{{ old('login_help_line_one', $companySettings->login_help_line_one) }}" placeholder="Forgot password?">
                                            @error('login_help_line_one')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Helper Line Two</label>
                                            <input type="text" class="form-control @error('login_help_line_two') is-invalid @enderror" name="login_help_line_two" value="{{ old('login_help_line_two', $companySettings->login_help_line_two) }}" placeholder="PurpleHeros">
                                            @error('login_help_line_two')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Helper Line Three</label>
                                            <input type="text" class="form-control @error('login_help_line_three') is-invalid @enderror" name="login_help_line_three" value="{{ old('login_help_line_three', $companySettings->login_help_line_three) }}" placeholder="Purple Crayola Employee Access">
                                            @error('login_help_line_three')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="section-header mb-4">
                                    <h4 class="mb-1">File Storage</h4>
                                    <p class="text-muted mb-0">Choose where uploaded files are stored: local server storage or Cloudinary.</p>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Storage Provider <span class="text-danger">*</span></label>
                                            <select class="form-control @error('storage_provider') is-invalid @enderror" name="storage_provider">
                                                <option value="local" {{ old('storage_provider', $companySettings->storage_provider ?? 'local') === 'local' ? 'selected' : '' }}>Local Server</option>
                                                <option value="cloudinary" {{ old('storage_provider', $companySettings->storage_provider ?? 'local') === 'cloudinary' ? 'selected' : '' }}>Cloudinary</option>
                                            </select>
                                            @error('storage_provider')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Cloudinary Folder</label>
                                            <input type="text" class="form-control @error('cloudinary_folder') is-invalid @enderror" name="cloudinary_folder" value="{{ old('cloudinary_folder', $companySettings->cloudinary_folder ?: 'purple-hr') }}" placeholder="purple-hr">
                                            <small class="form-text text-muted">Optional root folder in your Cloudinary account.</small>
                                            @error('cloudinary_folder')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Secure Delivery</label>
                                            <select class="form-control @error('cloudinary_secure_delivery') is-invalid @enderror" name="cloudinary_secure_delivery">
                                                <option value="1" {{ (int) old('cloudinary_secure_delivery', (int) ($companySettings->cloudinary_secure_delivery ?? 1)) === 1 ? 'selected' : '' }}>Yes (HTTPS)</option>
                                                <option value="0" {{ (int) old('cloudinary_secure_delivery', (int) ($companySettings->cloudinary_secure_delivery ?? 1)) === 0 ? 'selected' : '' }}>No</option>
                                            </select>
                                            @error('cloudinary_secure_delivery')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Cloudinary Cloud Name</label>
                                            <input type="text" class="form-control @error('cloudinary_cloud_name') is-invalid @enderror" name="cloudinary_cloud_name" value="{{ old('cloudinary_cloud_name', $companySettings->cloudinary_cloud_name) }}" placeholder="your-cloud-name">
                                            @error('cloudinary_cloud_name')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Cloudinary API Key</label>
                                            <input type="text" class="form-control @error('cloudinary_api_key') is-invalid @enderror" name="cloudinary_api_key" value="{{ old('cloudinary_api_key', $companySettings->cloudinary_api_key) }}" placeholder="API key">
                                            @error('cloudinary_api_key')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Cloudinary API Secret</label>
                                            <input type="password" class="form-control @error('cloudinary_api_secret') is-invalid @enderror" name="cloudinary_api_secret" value="{{ old('cloudinary_api_secret', $companySettings->cloudinary_api_secret) }}" placeholder="API secret">
                                            @error('cloudinary_api_secret')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="section-header mb-4">
                                    <h4 class="mb-1">Payroll Payment Gateways</h4>
                                    <p class="text-muted mb-0">Configure OPay Nigeria and Kuda Bank API settings for salary disbursement.</p>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="payment-gateway-card p-3 border rounded mb-3">
                                            <h5 class="mb-3">OPay Nigeria</h5>
                                            <div class="form-group">
                                                <label>Enable OPay</label>
                                                <select class="form-control @error('opay_enabled') is-invalid @enderror" name="opay_enabled">
                                                    <option value="1" {{ (int) old('opay_enabled', (int) ($companySettings->opay_enabled ?? 0)) === 1 ? 'selected' : '' }}>Yes</option>
                                                    <option value="0" {{ (int) old('opay_enabled', (int) ($companySettings->opay_enabled ?? 0)) === 0 ? 'selected' : '' }}>No</option>
                                                </select>
                                                @error('opay_enabled')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Sandbox Mode</label>
                                                <select class="form-control @error('opay_sandbox_mode') is-invalid @enderror" name="opay_sandbox_mode">
                                                    <option value="1" {{ (int) old('opay_sandbox_mode', (int) ($companySettings->opay_sandbox_mode ?? 1)) === 1 ? 'selected' : '' }}>Enabled</option>
                                                    <option value="0" {{ (int) old('opay_sandbox_mode', (int) ($companySettings->opay_sandbox_mode ?? 1)) === 0 ? 'selected' : '' }}>Disabled</option>
                                                </select>
                                                @error('opay_sandbox_mode')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Base URL</label>
                                                <input type="url" class="form-control @error('opay_base_url') is-invalid @enderror" name="opay_base_url" value="{{ old('opay_base_url', $companySettings->opay_base_url) }}" placeholder="https://api.opay.ng">
                                                @error('opay_base_url')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Transfer Path</label>
                                                <input type="text" class="form-control @error('opay_transfer_path') is-invalid @enderror" name="opay_transfer_path" value="{{ old('opay_transfer_path', $companySettings->opay_transfer_path ?: '/api/v1/transfers') }}" placeholder="/api/v1/transfers">
                                                @error('opay_transfer_path')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Merchant ID</label>
                                                <input type="text" class="form-control @error('opay_merchant_id') is-invalid @enderror" name="opay_merchant_id" value="{{ old('opay_merchant_id', $companySettings->opay_merchant_id) }}" placeholder="Merchant ID">
                                                @error('opay_merchant_id')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Public Key</label>
                                                <input type="text" class="form-control @error('opay_public_key') is-invalid @enderror" name="opay_public_key" value="{{ old('opay_public_key', $companySettings->opay_public_key) }}" placeholder="Public key">
                                                @error('opay_public_key')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                            </div>
                                            <div class="form-group mb-0">
                                                <label>Secret Key</label>
                                                <input type="password" class="form-control @error('opay_secret_key') is-invalid @enderror" name="opay_secret_key" value="{{ old('opay_secret_key', $companySettings->opay_secret_key) }}" placeholder="Secret key">
                                                @error('opay_secret_key')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="payment-gateway-card p-3 border rounded mb-3">
                                            <h5 class="mb-3">Kuda Bank</h5>
                                            <div class="form-group">
                                                <label>Enable Kuda</label>
                                                <select class="form-control @error('kuda_enabled') is-invalid @enderror" name="kuda_enabled">
                                                    <option value="1" {{ (int) old('kuda_enabled', (int) ($companySettings->kuda_enabled ?? 0)) === 1 ? 'selected' : '' }}>Yes</option>
                                                    <option value="0" {{ (int) old('kuda_enabled', (int) ($companySettings->kuda_enabled ?? 0)) === 0 ? 'selected' : '' }}>No</option>
                                                </select>
                                                @error('kuda_enabled')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Sandbox Mode</label>
                                                <select class="form-control @error('kuda_sandbox_mode') is-invalid @enderror" name="kuda_sandbox_mode">
                                                    <option value="1" {{ (int) old('kuda_sandbox_mode', (int) ($companySettings->kuda_sandbox_mode ?? 1)) === 1 ? 'selected' : '' }}>Enabled</option>
                                                    <option value="0" {{ (int) old('kuda_sandbox_mode', (int) ($companySettings->kuda_sandbox_mode ?? 1)) === 0 ? 'selected' : '' }}>Disabled</option>
                                                </select>
                                                @error('kuda_sandbox_mode')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Base URL</label>
                                                <input type="url" class="form-control @error('kuda_base_url') is-invalid @enderror" name="kuda_base_url" value="{{ old('kuda_base_url', $companySettings->kuda_base_url) }}" placeholder="https://kuda-openapi.kuda.com">
                                                @error('kuda_base_url')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Transfer Path</label>
                                                <input type="text" class="form-control @error('kuda_transfer_path') is-invalid @enderror" name="kuda_transfer_path" value="{{ old('kuda_transfer_path', $companySettings->kuda_transfer_path ?: '/v2/disbursements') }}" placeholder="/v2/disbursements">
                                                @error('kuda_transfer_path')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                            </div>
                                            <div class="form-group">
                                                <label>API Key</label>
                                                <input type="text" class="form-control @error('kuda_api_key') is-invalid @enderror" name="kuda_api_key" value="{{ old('kuda_api_key', $companySettings->kuda_api_key) }}" placeholder="API key">
                                                @error('kuda_api_key')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Secret Key</label>
                                                <input type="password" class="form-control @error('kuda_secret_key') is-invalid @enderror" name="kuda_secret_key" value="{{ old('kuda_secret_key', $companySettings->kuda_secret_key) }}" placeholder="Secret key">
                                                @error('kuda_secret_key')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                            </div>
                                            <div class="form-group mb-0">
                                                <label>Client Email</label>
                                                <input type="email" class="form-control @error('kuda_client_email') is-invalid @enderror" name="kuda_client_email" value="{{ old('kuda_client_email', $companySettings->kuda_client_email) }}" placeholder="finance@company.com">
                                                @error('kuda_client_email')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="submit-section">
                            <button type="submit" class="btn btn-primary submit-btn">Save Company Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
