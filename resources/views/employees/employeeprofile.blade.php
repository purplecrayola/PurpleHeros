@extends('layouts.master')
@section('content')
    @php($companySettings = \App\Models\CompanySettings::current())
    @php($brandPrimary = $companySettings->brand_primary_color ?? '#8A00FF')
    @php($brandDark = $companySettings->brand_dark_color ?? '#00163F')
    @php($brandNeutral = $companySettings->brand_neutral_color ?? '#DCDDDF')
    <style>
        .employee-profile-modern {
            background:
                radial-gradient(circle at 12% 12%, rgba({{ $companySettings->colorRgb('brand_primary_color', '#8A00FF') }}, 0.08), transparent 32%),
                linear-gradient(180deg, rgba(247, 249, 255, 0.92), rgba(241, 244, 252, 0.98));
        }
        .employee-profile-modern .content.container-fluid {
            padding-top: 28px;
            padding-bottom: 28px;
        }
        .profile-completion-card {
            border: 1px solid color-mix(in srgb, {{ $brandPrimary }} 25%, white);
            box-shadow: 0 14px 30px rgba(0, 22, 63, 0.08);
            background: linear-gradient(120deg, rgba(255, 255, 255, 0.97), rgba(245, 247, 255, 0.96));
            border-radius: 20px;
        }
        .profile-tab-shell {
            border: 1px solid color-mix(in srgb, {{ $brandDark }} 12%, white);
            box-shadow: 0 8px 20px rgba(0, 22, 63, 0.06);
            border-radius: 16px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.75);
        }
        .profile-tab-shell .nav-link {
            border-radius: 999px;
            margin: 10px 6px;
            padding: 10px 18px;
            font-weight: 600;
            color: rgba(0, 22, 63, 0.68);
        }
        .profile-tab-shell .nav-link.active {
            color: {{ $brandPrimary }} !important;
            border-bottom: none;
            background: rgba({{ $companySettings->colorRgb('brand_primary_color', '#8A00FF') }}, 0.12);
            font-weight: 600;
        }
        .profile-box {
            border: 1px solid color-mix(in srgb, {{ $brandNeutral }} 60%, white);
            border-radius: 18px;
            box-shadow: 0 12px 24px rgba(0, 22, 63, 0.06);
            background: rgba(255, 255, 255, 0.94);
        }
        .employee-profile-modern .page-header .page-title {
            font-size: 2.2rem;
            letter-spacing: -0.02em;
            font-weight: 700;
        }
        .employee-profile-modern .profile-view {
            border-radius: 20px;
            background: linear-gradient(120deg, rgba(255,255,255,0.96), rgba(244,247,255,0.94));
            border: 1px solid rgba(0, 22, 63, 0.08);
            box-shadow: 0 16px 34px rgba(0, 22, 63, 0.08);
            padding: 24px 24px 18px;
            position: relative;
        }
        .employee-profile-modern .profile-img-wrap {
            width: 130px;
            height: 130px;
            position: static;
            border-radius: 26px;
            background: linear-gradient(145deg, rgba({{ $companySettings->colorRgb('brand_primary_color', '#8A00FF') }}, 0.2), rgba(0, 22, 63, 0.08));
            border: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 14px 26px rgba(15, 23, 42, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .employee-profile-modern #profile_info .profile-img-wrap.edit-img {
            position: relative;
            margin: 0 auto 14px;
        }
        .employee-profile-modern .profile-avatar-upload {
            max-width: 280px;
            margin: 0 auto 16px;
            text-align: center;
        }
        .employee-profile-modern .profile-avatar-upload input[type="file"] {
            font-size: 13px;
        }
        .employee-profile-modern .profile-img-wrap .profile-img {
            width: 112px;
            height: 112px;
            border-radius: 22px;
            overflow: hidden;
        }
        .employee-profile-modern .profile-img-wrap .profile-img img,
        .employee-profile-modern #profile_info .profile-img-wrap.edit-img img.inline-block {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }
        .employee-profile-modern #profile_info .profile-img-wrap.edit-img {
            overflow: hidden;
        }
        .employee-profile-modern .profile-info-left .user-name {
            font-size: 2rem;
            letter-spacing: -0.02em;
            font-weight: 800;
            line-height: 1.1;
            color: #0f172a;
        }
        .employee-profile-modern .profile-info-left h6 {
            font-size: 1rem;
            margin-bottom: 6px;
            color: rgba(15, 23, 42, 0.78) !important;
            font-weight: 600;
        }
        .employee-profile-modern .profile-info-left small {
            display: inline-block;
            margin-bottom: 10px;
            color: rgba(15, 23, 42, 0.66) !important;
            font-weight: 500;
        }
        .employee-profile-modern .profile-info-left .staff-id {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: rgba({{ $companySettings->colorRgb('brand_primary_color', '#8A00FF') }}, 0.1);
            color: #0f172a;
            font-weight: 700;
            font-size: 0.82rem;
            padding: 4px 12px;
            margin: 2px 0 8px;
        }
        .employee-profile-modern .profile-info-left .doj {
            font-size: 0.86rem;
            color: rgba(15, 23, 42, 0.62) !important;
        }
        .employee-profile-modern .profile-view .staff-msg .btn-custom {
            background: linear-gradient(135deg, {{ $brandPrimary }}, {{ $brandDark }});
            border: none;
            color: #fff;
            box-shadow: 0 10px 22px rgba(138, 0, 255, 0.25);
            border-radius: 14px;
            min-height: 44px;
            padding: 10px 20px;
            font-size: 0.95rem;
            font-weight: 600;
        }
        .employee-profile-modern .profile-basic > .row > .col-md-7 {
            border-left: 1px dashed rgba(15, 23, 42, 0.18);
            padding-left: 28px;
        }
        .employee-profile-modern .profile-view .personal-info {
            margin-top: 4px;
        }
        .employee-profile-modern .profile-view .personal-info li {
            display: grid;
            grid-template-columns: 108px 1fr;
            gap: 12px;
            align-items: center;
            padding: 6px 0;
        }
        .employee-profile-modern .profile-view .personal-info li .title,
        .employee-profile-modern .profile-view .personal-info li .text {
            float: none !important;
            width: auto !important;
            margin-right: 0 !important;
            overflow: visible !important;
            min-width: 0;
        }
        .employee-profile-modern .profile-view .personal-info .title {
            font-weight: 600;
            color: rgba(15, 23, 42, 0.78);
            font-size: 0.95rem;
            white-space: nowrap;
        }
        .employee-profile-modern .profile-view .personal-info .text {
            color: rgba(15, 23, 42, 0.74);
            font-weight: 500;
            font-size: 0.95rem;
        }
        .employee-profile-modern .profile-view .personal-info .text a {
            color: inherit;
        }
        .employee-profile-modern .profile-view .personal-info .text a:hover {
            color: {{ $brandPrimary }};
        }
        .employee-profile-modern .pro-edit {
            top: 18px;
            right: 16px;
        }
        .employee-profile-modern .badge.bg-primary {
            background: {{ $brandPrimary }} !important;
            color: #fff !important;
        }
        .employee-profile-modern .progress {
            border-radius: 999px;
            overflow: hidden;
            background: rgba(0, 22, 63, 0.08);
        }
        .employee-profile-modern .progress-bar {
            background: linear-gradient(90deg, {{ $brandPrimary }}, {{ $brandDark }}) !important;
        }
        .employee-profile-modern .card.mb-0 {
            border-radius: 22px;
            border: 1px solid rgba(0, 22, 63, 0.07);
            box-shadow: 0 16px 32px rgba(0, 22, 63, 0.08);
        }
        .employee-profile-modern .pro-edit .edit-icon,
        .employee-profile-modern .edit-icon {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba({{ $companySettings->colorRgb('brand_primary_color', '#8A00FF') }}, 0.1);
            color: {{ $brandPrimary }};
        }
        .employee-profile-modern .pro-edit .edit-icon:hover,
        .employee-profile-modern .edit-icon:hover {
            background: rgba({{ $companySettings->colorRgb('brand_primary_color', '#8A00FF') }}, 0.2);
        }
        @media (max-width: 991px) {
            .employee-profile-modern .page-header .page-title {
                font-size: 1.8rem;
            }
            .profile-tab-shell .nav-link {
                padding: 8px 14px;
                margin: 8px 4px;
            }
            .employee-profile-modern .profile-info-left .user-name {
                font-size: 1.6rem;
            }
            .employee-profile-modern .profile-basic > .row > .col-md-7 {
                border-left: 0;
                border-top: 1px dashed rgba(15, 23, 42, 0.18);
                padding-left: 15px;
                margin-top: 16px;
                padding-top: 14px;
            }
            .employee-profile-modern .profile-view .personal-info li {
                grid-template-columns: 90px 1fr;
            }
        }
    </style>
    <div class="page-wrapper employee-profile-modern">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">Profile</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Profile</li>
                        </ul>
                    </div>
                </div>
            </div>
            {{-- message --}}
            {!! Toastr::message() !!}
            <div class="card mb-3 profile-completion-card">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Profile Completion</h5>
                        <span class="badge bg-primary">{{ (int) ($profileCompletion ?? 0) }}%</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ (int) ($profileCompletion ?? 0) }}%;" aria-valuenow="{{ (int) ($profileCompletion ?? 0) }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
            <div class="card mb-0">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="profile-view">
                                <div class="profile-img-wrap">
                                    <div class="profile-img">
                                        <a href="#"><img class="user-profile" alt="" src="{{ \App\Support\MediaStorageManager::publicUrl($users->avatar ?? null, 'assets/img/user.jpg') }}" alt="{{ $users->name }}"></a>
                                    </div>
                                </div>
                                <div class="profile-basic">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="profile-info-left">
                                                <h3 class="user-name m-t-0 mb-0">{{ $users->name }}</h3>
                                                <h6 class="text-muted"> {{ $users->department }}</h6>
                                                <small class="text-muted">{{ $users->position }}</small>
                                                <div class="staff-id">Employee ID : {{ $users->user_id }}</div>
                                                <div class="small doj text-muted">Date of Join : {{ $users->join_date }}</div>
                                                <div class="staff-msg"><a class="btn btn-custom" href="mailto:{{ $users->email }}">Send Message</a></div>
                                            </div>
                                        </div>
                                        <div class="col-md-7">
                                            <ul class="personal-info">
                                                <li>
                                                    <div class="title">Phone:</div>
                                                    <div class="text">
                                                        @if(!empty($users->phone_number))
                                                            <a>{{ $users->phone_number }}</a>
                                                        @else
                                                            <a>N/A</a>
                                                        @endif
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="title">Email:</div>
                                                    <div class="text">
                                                        @if(!empty($users->email))
                                                        <a>{{ $users->email }}</a>
                                                        @else
                                                            <a>N/A</a>
                                                        @endif
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="title">Birthday:</div>
                                                    <div class="text">
                                                        @if(!empty($users->birth_date))
                                                        <a>{{ $users->birth_date }}</a>
                                                        @else
                                                            <a>N/A</a>
                                                        @endif
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="title">Address:</div>
                                                    <div class="text">
                                                        @if(!empty($users->address))
                                                        <a>{{ $users->address }}</a>
                                                        @else
                                                            <a>N/A</a>
                                                        @endif
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="title">Gender:</div>
                                                    <div class="text">
                                                        @if(!empty($users->gender))
                                                        <a>{{ $users->gender }}</a>
                                                        @else
                                                            <a>N/A</a>
                                                        @endif
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="title">Reports to:</div>
                                                    <div class="text">
                                                        <div class="avatar-box">
                                                            <div class="avatar avatar-xs">
                                                                <img src="{{ \App\Support\MediaStorageManager::publicUrl($users->avatar ?? null, 'assets/img/user.jpg') }}" alt="{{ $users->name }}">
                                                            </div>
                                                        </div>
                                                        <a>{{ $users->name }}</a>
                                                    </div>
                                                </li> 
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="pro-edit"><a data-target="#profile_info" data-toggle="modal" class="edit-icon" href="#"><i class="fa fa-pencil"></i></a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
					
            <div class="card tab-box profile-tab-shell">
                <div class="row user-tabs">
                    <div class="col-lg-12 col-md-12 col-sm-12 line-tabs">
                        <ul class="nav nav-tabs nav-tabs-bottom">
                            <li class="nav-item"><a href="#emp_profile" data-toggle="tab" class="nav-link active">Profile</a></li>
                            <li class="nav-item"><a href="#emp_onboarding" data-toggle="tab" class="nav-link">Onboarding & References</a></li>
                            <li class="nav-item"><a href="#emp_projects" data-toggle="tab" class="nav-link">Projects</a></li>
                            <li class="nav-item"><a href="#bank_statutory" data-toggle="tab" class="nav-link">Bank & Statutory <small class="text-danger">(Admin Only)</small></a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="tab-content">
                <!-- Profile Info Tab -->
                <div id="emp_profile" class="pro-overview tab-pane fade show active">
                    <div class="row">
                        <div class="col-md-6 d-flex">
                            <div class="card profile-box flex-fill">
                                <div class="card-body">
                                    <h3 class="card-title">Personal Informations <a href="#" class="edit-icon" data-toggle="modal" data-target="#personal_info_modal"><i class="fa fa-pencil"></i></a></h3>
                                    <ul class="personal-info">
                                        <li>
                                            <div class="title">Passport No.</div>
                                            @if (!empty($users->passport_no))
                                                <div class="text">{{ $users->passport_no }}</div>
                                            @else
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Passport Exp Date.</div>
                                            @if (!empty($users->passport_expiry_date))
                                                <div class="text">{{ $users->passport_expiry_date }}</div>
                                            @else
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Tel</div>
                                            @if (!empty($users->tel))
                                                <div class="text">{{ $users->tel }}</div>
                                            @else
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Nationality</div>
                                            @if (!empty($users->nationality))
                                                <div class="text">{{ $users->nationality }}</div>
                                            @else
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Religion</div>
                                            @if (!empty($users->religion))
                                                <div class="text">{{ $users->religion }}</div>
                                            @else
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Marital status</div>
                                            @if (!empty($users->marital_status))
                                                <div class="text">{{ $users->marital_status }}</div>
                                            @else
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Employment of spouse</div>
                                            @if (!empty($users->employment_of_spouse))
                                                <div class="text">{{ $users->employment_of_spouse }}</div>
                                            @else
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">No. of children</div>
                                            @if ($users->children != null)
                                                <div class="text">{{ $users->children }}</div>
                                            @else
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex">
                            <div class="card profile-box flex-fill">
                                <div class="card-body">
                                    <h3 class="card-title">Emergency Contact <a href="#" class="edit-icon" data-toggle="modal" data-target="#emergency_contact_modal"><i class="fa fa-pencil"></i></a></h3>
                                    <h5 class="section-title">Primary</h5>
                                    <ul class="personal-info">
                                        <li>
                                            <div class="title">Name</div>
                                            @if (!empty($users->name_primary))
                                            <div class="text">{{ $users->name_primary }}</div>
                                            @else
                                            <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Relationship</div>
                                            @if (!empty($users->relationship_primary))
                                            <div class="text">{{ $users->relationship_primary }}</div>
                                            @else
                                            <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Phone </div>
                                            @if (!empty($users->phone_primary) && !empty($users->phone_2_primary))
                                            <div class="text">{{ $users->phone_primary }},{{ $users->phone_2_primary }}</div>
                                            @else
                                            <div class="text">N/A</div>
                                            @endif
                                        </li>
                                    </ul>
                                    <hr>
                                    <h5 class="section-title">Secondary</h5>
                                    <ul class="personal-info">
                                        <li>
                                            <div class="title">Name</div>
                                            @if (!empty($users->name_secondary))
                                            <div class="text">{{ $users->name_secondary }}</div>
                                            @else
                                            <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Relationship</div>
                                            @if (!empty($users->relationship_secondary))
                                            <div class="text">{{ $users->relationship_secondary }}</div>
                                            @else
                                            <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Phone </div>
                                            @if (!empty($users->phone_secondary) && !empty($users->phone_2_secondary))
                                            <div class="text">{{ $users->phone_secondary }},{{ $users->phone_2_secondary }}</div>
                                            @else
                                            <div class="text">N/A</div>
                                            @endif
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 d-flex">
                            <div class="card profile-box flex-fill">
                                <div class="card-body">
                                    <h3 class="card-title">Bank information 
                                        <a href="#" class="edit-icon" data-toggle="modal" data-target="#bank_information_modal">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    </h3>
                                    <ul class="personal-info">
                                        <li>
                                            <div class="title">Bank name</div>
                                            <div class="text">ICICI Bank</div>
                                        </li>
                                        <li>
                                            <div class="title">Bank account No.</div>
                                            <div class="text">159843014641</div>
                                        </li>
                                        <li>
                                            <div class="title">Bank Code (NIP)</div>
                                            <div class="text">ICI24504</div>
                                        </li>
                                        <li>
                                            <div class="title">PAN No</div>
                                            <div class="text">TC000Y56</div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex">
                            <div class="card profile-box flex-fill">
                                <div class="card-body">
                                    <h3 class="card-title">Family Informations <a href="#" class="edit-icon" data-toggle="modal" data-target="#family_info_modal"><i class="fa fa-pencil"></i></a></h3>
                                    <div class="table-responsive">
                                        <table class="table table-nowrap">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Relationship</th>
                                                    <th>Date of Birth</th>
                                                    <th>Phone</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse(($familyMembers ?? collect()) as $member)
                                                    <tr>
                                                        <td>{{ $member->name ?: 'N/A' }}</td>
                                                        <td>
                                                            {{ $member->relationship ?: 'N/A' }}
                                                            @if($member->is_next_of_kin)
                                                                <small class="text-success">(Next of kin)</small>
                                                            @endif
                                                        </td>
                                                        <td>{{ $member->date_of_birth ? \Carbon\Carbon::parse($member->date_of_birth)->format('M j, Y') : 'N/A' }}</td>
                                                        <td>{{ $member->phone ?: 'N/A' }}</td>
                                                        <td></td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-muted">No family information recorded.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 d-flex">
                            <div class="card profile-box flex-fill">
                                <div class="card-body">
                                    <h3 class="card-title">Education Informations <a href="#emp_onboarding" class="edit-icon" data-toggle="tab"><i class="fa fa-pencil"></i></a></h3>
                                    <div class="experience-box">
                                        <ul class="experience-list">
                                            @forelse(($educations ?? collect()) as $education)
                                                <li>
                                                    <div class="experience-user">
                                                        <div class="before-circle"></div>
                                                    </div>
                                                    <div class="experience-content">
                                                        <div class="timeline-content">
                                                            <a href="#/" class="name">{{ $education->institution ?: 'Institution' }}</a>
                                                            <div>{{ trim(($education->degree ?: '') . ' ' . ($education->field_of_study ? '(' . $education->field_of_study . ')' : '')) ?: 'N/A' }}</div>
                                                            <span class="time">
                                                                {{ $education->start_date ? \Carbon\Carbon::parse($education->start_date)->format('M Y') : 'N/A' }}
                                                                -
                                                                {{ $education->end_date ? \Carbon\Carbon::parse($education->end_date)->format('M Y') : 'Present' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </li>
                                            @empty
                                                <li>
                                                    <div class="experience-content">
                                                        <div class="timeline-content text-muted">No education records yet.</div>
                                                    </div>
                                                </li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex">
                            <div class="card profile-box flex-fill">
                                <div class="card-body">
                                    <h3 class="card-title">Experience <a href="#emp_onboarding" class="edit-icon" data-toggle="tab"><i class="fa fa-pencil"></i></a></h3>
                                    <div class="experience-box">
                                        <ul class="experience-list">
                                            @forelse(($experiences ?? collect()) as $experience)
                                                <li>
                                                    <div class="experience-user">
                                                        <div class="before-circle"></div>
                                                    </div>
                                                    <div class="experience-content">
                                                        <div class="timeline-content">
                                                            <a href="#/" class="name">{{ ($experience->job_title ?: 'Role') . ' at ' . ($experience->company_name ?: 'Company') }}</a>
                                                            <span class="time">
                                                                {{ $experience->start_date ? \Carbon\Carbon::parse($experience->start_date)->format('M Y') : 'N/A' }}
                                                                -
                                                                {{ $experience->is_current ? 'Present' : ($experience->end_date ? \Carbon\Carbon::parse($experience->end_date)->format('M Y') : 'N/A') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </li>
                                            @empty
                                                <li>
                                                    <div class="experience-content">
                                                        <div class="timeline-content text-muted">No work experience records yet.</div>
                                                    </div>
                                                </li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Profile Info Tab -->

                <div class="tab-pane fade" id="emp_onboarding">
                    <div class="row">
                        <div class="col-md-6 d-flex">
                            <div class="card profile-box flex-fill">
                                <div class="card-body">
                                    <h3 class="card-title">Uploaded Documents</h3>
                                    <ul class="list-group">
                                        @forelse(($documents ?? collect()) as $document)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ strtoupper($document->document_type) }}</strong>
                                                    <div class="text-muted small">{{ $document->title ?: basename($document->file_path) }}</div>
                                                </div>
                                                <div class="text-right">
                                                    <a href="{{ $document->file_url }}" target="_blank" class="btn btn-sm btn-outline-primary">Open</a>
                                                    <div>
                                                        <small class="{{ $document->is_verified ? 'text-success' : 'text-muted' }}">
                                                            {{ $document->is_verified ? 'Verified' : 'Pending verification' }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="list-group-item text-muted">No onboarding documents uploaded yet.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex">
                            <div class="card profile-box flex-fill">
                                <div class="card-body">
                                    <h3 class="card-title">References</h3>
                                    <div class="table-responsive">
                                        <table class="table table-nowrap">
                                            <thead>
                                                <tr>
                                                    <th>Referee</th>
                                                    <th>Contact</th>
                                                    <th>Status</th>
                                                    <th>Feedback</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse(($references ?? collect()) as $reference)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $reference->referee_name }}</strong>
                                                            <div class="text-muted small">{{ $reference->relationship ?: 'N/A' }} | {{ $reference->company_name ?: 'N/A' }}</div>
                                                        </td>
                                                        <td>
                                                            <div>{{ $reference->email ?: 'N/A' }}</div>
                                                            <div>{{ $reference->phone ?: 'N/A' }}</div>
                                                        </td>
                                                        <td>
                                                            <span class="badge {{ $reference->is_verified ? 'bg-success' : 'bg-secondary' }}">
                                                                {{ $reference->is_verified ? 'Verified' : 'Pending' }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $reference->verification_feedback ?: '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-muted">No references submitted yet.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <small class="text-muted">To verify references and add admin comments, use Admin panel: People > Employees > Edit > Onboarding & References.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Projects Tab -->
                <div class="tab-pane fade" id="emp_projects">
                    <div class="row">
                        @forelse(($projectSnapshots ?? collect()) as $project)
                            <div class="col-lg-4 col-sm-6 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="project-title">{{ $project->project_name ?: 'Unspecified Workstream' }}</h4>
                                        <small class="block text-ellipsis m-b-15">
                                            <span class="text-xs">{{ $project->entry_count }}</span>
                                            <span class="text-muted">timesheet entries</span>
                                        </small>
                                        <p class="text-muted mb-2">Worked {{ $project->worked_hours }}h of {{ $project->assigned_hours }}h assigned.</p>
                                        <div class="pro-deadline m-b-15">
                                            <div class="sub-title">Last activity:</div>
                                            <div class="text-muted">
                                                {{ $project->last_activity ? \Carbon\Carbon::parse($project->last_activity)->format('M j, Y') : 'N/A' }}
                                            </div>
                                        </div>
                                        <p class="m-b-5">Progress <span class="text-success float-right">{{ $project->progress }}%</span></p>
                                        <div class="progress progress-xs mb-0">
                                            <div style="width: {{ $project->progress }}%" role="progressbar" class="progress-bar bg-success"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="mb-2">No Projects Yet</h5>
                                        <p class="text-muted mb-0">Projects are generated from timesheet workstreams once activity is logged.</p>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
                <!-- /Projects Tab -->
                
                <!-- Bank Statutory Tab -->
                <div class="tab-pane fade" id="bank_statutory">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title"> Basic Salary Information</h3>
                            <form>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">Salary basis <span class="text-danger">*</span></label>
                                            <select class="select">
                                                <option>Select salary basis type</option>
                                                <option>Hourly</option>
                                                <option>Daily</option>
                                                <option>Weekly</option>
                                                <option>Monthly</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">Salary amount <small class="text-muted">per month</small></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">$</span>
                                                </div>
                                                <input type="text" class="form-control" placeholder="Type your salary amount" value="0.00">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">Payment type</label>
                                            <select class="select">
                                                <option>Select payment type</option>
                                                <option>Bank transfer</option>
                                                <option>Check</option>
                                                <option>Cash</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <h3 class="card-title"> PF Information</h3>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">PF contribution</label>
                                            <select class="select">
                                                <option>Select PF contribution</option>
                                                <option>Yes</option>
                                                <option>No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">PF No. <span class="text-danger">*</span></label>
                                            <select class="select">
                                                <option>Select PF contribution</option>
                                                <option>Yes</option>
                                                <option>No</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">Employee PF rate</label>
                                            <select class="select">
                                                <option>Select PF contribution</option>
                                                <option>Yes</option>
                                                <option>No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">Additional rate <span class="text-danger">*</span></label>
                                            <select class="select">
                                                <option>Select additional rate</option>
                                                <option>0%</option>
                                                <option>1%</option>
                                                <option>2%</option>
                                                <option>3%</option>
                                                <option>4%</option>
                                                <option>5%</option>
                                                <option>6%</option>
                                                <option>7%</option>
                                                <option>8%</option>
                                                <option>9%</option>
                                                <option>10%</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">Total rate</label>
                                            <input type="text" class="form-control" placeholder="N/A" value="11%">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">Employee PF rate</label>
                                            <select class="select">
                                                <option>Select PF contribution</option>
                                                <option>Yes</option>
                                                <option>No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">Additional rate <span class="text-danger">*</span></label>
                                            <select class="select">
                                                <option>Select additional rate</option>
                                                <option>0%</option>
                                                <option>1%</option>
                                                <option>2%</option>
                                                <option>3%</option>
                                                <option>4%</option>
                                                <option>5%</option>
                                                <option>6%</option>
                                                <option>7%</option>
                                                <option>8%</option>
                                                <option>9%</option>
                                                <option>10%</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">Total rate</label>
                                            <input type="text" class="form-control" placeholder="N/A" value="11%">
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                <h3 class="card-title"> ESI Information</h3>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">ESI contribution</label>
                                            <select class="select">
                                                <option>Select ESI contribution</option>
                                                <option>Yes</option>
                                                <option>No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">ESI No. <span class="text-danger">*</span></label>
                                            <select class="select">
                                                <option>Select ESI contribution</option>
                                                <option>Yes</option>
                                                <option>No</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">Employee ESI rate</label>
                                            <select class="select">
                                                <option>Select ESI contribution</option>
                                                <option>Yes</option>
                                                <option>No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">Additional rate <span class="text-danger">*</span></label>
                                            <select class="select">
                                                <option>Select additional rate</option>
                                                <option>0%</option>
                                                <option>1%</option>
                                                <option>2%</option>
                                                <option>3%</option>
                                                <option>4%</option>
                                                <option>5%</option>
                                                <option>6%</option>
                                                <option>7%</option>
                                                <option>8%</option>
                                                <option>9%</option>
                                                <option>10%</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">Total rate</label>
                                            <input type="text" class="form-control" placeholder="N/A" value="11%">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="submit-section">
                                    <button class="btn btn-primary submit-btn" type="submit">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /Bank Statutory Tab -->
            </div>
        </div>
        <!-- /Page Content -->

        <!-- Profile Modal -->
        <div id="profile_info" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Profile Information</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('profile/information/save') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="profile-img-wrap edit-img">
                                        <img class="inline-block" src="{{ \App\Support\MediaStorageManager::publicUrl($users->avatar ?? null, 'assets/img/user.jpg') }}" alt="{{ $users->name }}">
                                    </div>
                                    <div class="profile-avatar-upload">
                                        <label class="mb-1 d-block">Profile Picture</label>
                                        <input class="form-control" type="file" name="images" accept="image/*">
                                        <small class="text-muted d-block mt-1">JPG, PNG, or WEBP. Max 5MB.</small>
                                        @if(!empty($users))
                                            <input type="hidden" name="hidden_image" id="e_image" value="{{ $users->avatar }}">
                                        @endif
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Full Name</label>
                                                <input type="text" class="form-control" id="name" name="name" value="{{ $users->name }}">
                                                <input type="hidden" class="form-control" id="user_id" name="user_id" value="{{ $users->user_id }}">
                                                <input type="hidden" class="form-control" id="email" name="email" value="{{ $users->email }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Birth Date</label>
                                                <div class="cal-icon">
                                                    @if(!empty($users))
                                                        <input class="form-control datetimepicker" type="text" id="birthDate" name="birthDate" value="{{ $users->birth_date }}">
                                                    @else
                                                        <input class="form-control datetimepicker" type="text" id="birthDate" name="birthDate">
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Gender</label>
                                                <select class="select form-control" id="gender" name="gender">
                                                    @if(!empty($users))
                                                        <option value="{{ $users->gender }}" {{ ( $users->gender == $users->gender) ? 'selected' : '' }}>{{ $users->gender }} </option>
                                                        <option value="Male">Male</option>
                                                        <option value="Female">Female</option>
                                                    @else
                                                        <option value="Male">Male</option>
                                                        <option value="Female">Female</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Address</label>
                                        @if(!empty($users))
                                            <input type="text" class="form-control" id="address" name="address" value="{{ $users->address }}">
                                        @else
                                            <input type="text" class="form-control" id="address" name="address">
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>State</label>
                                        @if(!empty($users))
                                            <input type="text" class="form-control" id="state" name="state" value="{{ $users->state }}">
                                        @else
                                            <input type="text" class="form-control" id="state" name="state">
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Country</label>
                                        @if(!empty($users))
                                            <input type="text" class="form-control" id="" name="country" value="{{ $users->country }}">
                                        @else
                                            <input type="text" class="form-control" id="" name="country">
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Postal Code</label>
                                        @if(!empty($users))
                                            <input type="text" class="form-control" id="pin_code" name="pin_code" value="{{ $users->pin_code }}">
                                        @else
                                            <input type="text" class="form-control" id="pin_code" name="pin_code">
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone Number</label>
                                        @if(!empty($users))
                                            <input type="text" class="form-control" id="phoneNumber" name="phone_number" value="{{ $users->phone_number }}">
                                        @else
                                            <input type="text" class="form-control" id="phoneNumber" name="phone_number">
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Department <span class="text-danger">*</span></label>
                                        <select class="select" id="department" name="department">
                                            @if(!empty($users))
                                                <option value="{{ $users->department }}" {{ ( $users->department == $users->department) ? 'selected' : '' }}>{{ $users->department }} </option>
                                                <option value="Web Development">Web Development</option>
                                                <option value="IT Management">IT Management</option>
                                                <option value="Marketing">Marketing</option>
                                            @else
                                                <option value="Web Development">Web Development</option>
                                                <option value="IT Management">IT Management</option>
                                                <option value="Marketing">Marketing</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Designation <span class="text-danger">*</span></label>
                                        <select class="select" id="designation" name="designation">
                                            @if(!empty($users))
                                                <option value="{{ $users->designation }}" {{ ( $users->designation == $users->designation) ? 'selected' : '' }}>{{ $users->designation }} </option>
                                                <option value="Web Designer">Web Designer</option>
                                                <option value="Web Developer">Web Developer</option>
                                                <option value="Android Developer">Android Developer</option>
                                            @else
                                                <option value="Web Designer">Web Designer</option>
                                                <option value="Web Developer">Web Developer</option>
                                                <option value="Android Developer">Android Developer</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Reports To <span class="text-danger">*</span></label>
                                        <select class="select" id="" name="reports_to">
                                            @if(!empty($users))
                                                <option value="{{ $users->reports_to }}" {{ ( $users->reports_to == $users->reports_to) ? 'selected' : '' }}>{{ $users->reports_to }} </option>
                                                    @foreach ($user as $users )
                                                    <option value="{{ $users->name }}">{{ $users->name }}</option>
                                                @endforeach
                                            @else
                                                @foreach ($user as $users )
                                                    <option value="{{ $users->name }}">{{ $users->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Profile Modal -->
    
        <!-- Personal Info Modal -->
        <div id="personal_info_modal" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Personal Information</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('user/information/save') }}" method="POST">
                            @csrf
                            <input type="hidden" class="form-control" name="user_id" value="{{ $users->user_id }}" readonly>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Passport No</label>
                                        <input type="text" class="form-control @error('passport_no') is-invalid @enderror" name="passport_no" value="{{ $users->passport_no }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Passport Expiry Date</label>
                                        <div class="cal-icon">
                                            <input class="form-control datetimepicker @error('passport_expiry_date') is-invalid @enderror" type="text" name="passport_expiry_date" value="{{ $users->passport_expiry_date }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tel</label>
                                        <input class="form-control @error('tel') is-invalid @enderror" type="text" name="tel" value="{{ $users->tel }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nationality <span class="text-danger">*</span></label>
                                        <input class="form-control @error('nationality') is-invalid @enderror" type="text" name="nationality" value="{{ $users->nationality }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Religion</label>
                                        <div class="form-group">
                                            <input class="form-control @error('religion') is-invalid @enderror" type="text" name="religion" value="{{ $users->religion }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Marital status <span class="text-danger">*</span></label>
                                        <select class="select form-control @error('marital_status') is-invalid @enderror" name="marital_status">
                                            <option value="{{ $users->marital_status }}" {{ ( $users->marital_status == $users->marital_status) ? 'selected' : '' }}> {{ $users->marital_status }} </option>
                                            <option value="Single">Single</option>
                                            <option value="Married">Married</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Employment of spouse</label>
                                        <input class="form-control @error('employment_of_spouse') is-invalid @enderror" type="text" name="employment_of_spouse" value="{{ $users->employment_of_spouse }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>No. of children </label>
                                        <input class="form-control @error('children') is-invalid @enderror" type="text" name="children" value="{{ $users->children }}">
                                    </div>
                                </div>
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Personal Info Modal -->

        <!-- Bank information Modal -->
        <div id="bank_information_modal" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Bank Information</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            @csrf
                            <input type="hidden" class="form-control" name="user_id" value="{{ Session::get('user_id') }}" readonly>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Bank name</label>
                                        <input type="text" class="form-control @error('bank_name') is-invalid @enderror" name="bank_name" value="{{ old('bank_name') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Bank account No</label>
                                        <input type="text" class="form-control @error('bank_account_no') is-invalid @enderror" name="bank_account_no" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" value="{{ old('bank_account_no') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Bank Code (NIP)</label>
                                        <input type="text" class="form-control @error('ifsc_code') is-invalid @enderror" name="ifsc_code" value="{{ old('ifsc_code') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>PAN No</label>
                                        <input type="text" class="form-control @error('pan_no') is-invalid @enderror" name="pan_no" value="{{ old('pan_no') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Bank information Modal -->
        
        <!-- Family Info Modal -->
        <div id="family_info_modal" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"> Family Informations</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="form-scroll">
                                <div class="card">
                                    <div class="card-body">
                                        <h3 class="card-title">Family Member <a href="javascript:void(0);" class="delete-icon"><i class="fa fa-trash-o"></i></a></h3>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Name <span class="text-danger">*</span></label>
                                                    <input class="form-control" type="text">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Relationship <span class="text-danger">*</span></label>
                                                    <input class="form-control" type="text">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Date of birth <span class="text-danger">*</span></label>
                                                    <input class="form-control" type="text">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Phone <span class="text-danger">*</span></label>
                                                    <input class="form-control" type="text">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-body">
                                        <h3 class="card-title">Education Informations <a href="javascript:void(0);" class="delete-icon"><i class="fa fa-trash-o"></i></a></h3>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Name <span class="text-danger">*</span></label>
                                                    <input class="form-control" type="text">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Relationship <span class="text-danger">*</span></label>
                                                    <input class="form-control" type="text">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Date of birth <span class="text-danger">*</span></label>
                                                    <input class="form-control" type="text">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Phone <span class="text-danger">*</span></label>
                                                    <input class="form-control" type="text">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="add-more">
                                            <a href="javascript:void(0);"><i class="fa fa-plus-circle"></i> Add More</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="submit-section">
                                <button class="btn btn-primary submit-btn">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Family Info Modal -->
        
        <!-- Emergency Contact Modal -->
        <div id="emergency_contact_modal" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Personal Information</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="validation" action="{{ route('user/profile/emergency/contact/save') }}" method="POST">
                            @csrf
                            <input type="text" class="form-control" name="user_id" value="{{ $users->user_id }}">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">Primary Contact</h3>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Name <span class="text-danger">*</span></label>
                                                @if (!empty($users->name_primary))
                                                <input type="text" class="form-control" name="name_primary" value="{{ $users->name_primary }}">
                                                @else
                                                <input type="text" class="form-control" name="name_primary">
                                                @endif
                                            </li>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Relationship <span class="text-danger">*</span></label>
                                                @if (!empty($users->relationship_primary))
                                                <input type="text" class="form-control" name="relationship_primary" value="{{ $users->relationship_primary }}">
                                                @else
                                                <input type="text" class="form-control" name="relationship_primary">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Phone <span class="text-danger">*</span></label>
                                                @if (!empty($users->phone_primary))
                                                <input type="text" class="form-control" name="phone_primary" value="{{ $users->phone_primary }}">
                                                @else
                                                <input type="text" class="form-control" name="phone_primary">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Phone 2</label>
                                                @if (!empty($users->phone_2_primary))
                                                <input type="text" class="form-control" name="phone_2_primary" value="{{ $users->phone_2_primary }}">
                                                @else
                                                <input type="text" class="form-control" name="phone_2_primary">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">Secondary Contact</h3>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Name <span class="text-danger">*</span></label>
                                                @if (!empty($users->name_secondary))
                                                <input type="text" class="form-control" name="name_secondary" value="{{ $users->name_secondary }}">
                                                @else
                                                <input type="text" class="form-control" name="name_secondary">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Relationship <span class="text-danger">*</span></label>
                                                @if (!empty($users->relationship_secondary))
                                                <input type="text" class="form-control" name="relationship_secondary" value="{{ $users->relationship_secondary }}">
                                                @else
                                                <input type="text" class="form-control" name="relationship_secondary">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Phone <span class="text-danger">*</span></label>
                                                @if (!empty($users->phone_secondary))
                                                <input type="text" class="form-control" name="phone_secondary" value="{{ $users->phone_secondary }}">
                                                @else
                                                <input type="text" class="form-control" name="phone_secondary">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Phone 2</label>
                                                @if (!empty($users->phone_2_secondary))
                                                <input type="text" class="form-control" name="phone_2_secondary" value="{{ $users->phone_2_secondary }}">
                                                @else
                                                <input type="text" class="form-control" name="phone_2_secondary">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Emergency Contact Modal -->
        
    <!-- /Page Content -->
    </div>
    @section('script')
    <script>
        $('#validation').validate({  
            rules: {  
                name_primary: 'required',  
                relationship_primary: 'required',  
                phone_primary: 'required',  
                phone_2_primary: 'required',  
                name_secondary: 'required',  
                relationship_secondary: 'required',  
                phone_secondary: 'required',  
                phone_2_secondary: 'required',  
            },  
            messages: {
                name_primary: 'Please input name primary',  
                relationship_primary: 'Please input relationship primary',  
                phone_primary: 'Please input phone primary',  
                phone_2_primary: 'Please input phone 2 primary',  
                name_secondary: 'Please input name secondary',  
                relationship_secondary: 'Please input relationship secondary',  
                phone_secondaryr: 'Please input phone secondary',  
                phone_2_secondary: 'Please input phone 2 secondary',  
            },  
            submitHandler: function(form) {  
                form.submit();
            }  
        });  
    </script>
    @endsection
@endsection
