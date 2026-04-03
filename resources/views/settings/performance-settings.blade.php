@extends('layouts.settings')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Performance Settings</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Performance Settings</li>
                        </ul>
                    </div>
                </div>
            </div>

            {!! Toastr::message() !!}

            <form action="{{ route('performance/settings/save') }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-header"><h4 class="card-title mb-0">Annual Appraisal Structure</h4></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Objectives Weight (%)</label>
                                    <input type="number" class="form-control" name="objective_weight" min="0" max="100" value="{{ old('objective_weight', $performanceSettings->objective_weight) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Values Weight (%)</label>
                                    <input type="number" class="form-control" name="values_weight" min="0" max="100" value="{{ old('values_weight', $performanceSettings->values_weight) }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="annual_section_objectives_enabled" value="1" id="annual_objectives" {{ old('annual_section_objectives_enabled', $performanceSettings->annual_section_objectives_enabled) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="annual_objectives">Enable Annual Objectives section</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="annual_section_values_enabled" value="1" id="annual_values" {{ old('annual_section_values_enabled', $performanceSettings->annual_section_values_enabled) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="annual_values">Enable Values section</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h4 class="card-title mb-0">Cadence & Ownership</h4></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="allow_employee_objectives" value="1" id="allow_employee_objectives" {{ old('allow_employee_objectives', $performanceSettings->allow_employee_objectives) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_employee_objectives">Allow employees to create own objectives</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="allow_manager_objectives" value="1" id="allow_manager_objectives" {{ old('allow_manager_objectives', $performanceSettings->allow_manager_objectives) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_manager_objectives">Allow managers/admins to assign objectives</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Weekly update due day</label>
                                    <select class="form-control" name="weekly_update_due_weekday">
                                        @foreach([1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'] as $value => $label)
                                            <option value="{{ $value }}" {{ (int) old('weekly_update_due_weekday', $performanceSettings->weekly_update_due_weekday) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Monthly update due day</label>
                                    <input type="number" class="form-control" min="1" max="31" name="monthly_update_due_day" value="{{ old('monthly_update_due_day', $performanceSettings->monthly_update_due_day) }}" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Values Catalog (one per line)</label>
                                    <textarea class="form-control" rows="5" name="values_catalog_lines">{{ old('values_catalog_lines', implode(PHP_EOL, $performanceSettings->valuesCatalog())) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h4 class="card-title mb-0">Annual Workflow Stages</h4></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="annual_stage_manager_submit_required" value="1" id="annual_stage_manager_submit_required" {{ old('annual_stage_manager_submit_required', $performanceSettings->annual_stage_manager_submit_required ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="annual_stage_manager_submit_required">Require manager submission stage</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="annual_stage_calibration_enabled" value="1" id="annual_stage_calibration_enabled" {{ old('annual_stage_calibration_enabled', $performanceSettings->annual_stage_calibration_enabled ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="annual_stage_calibration_enabled">Enable calibration stage</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="annual_stage_joint_review_enabled" value="1" id="annual_stage_joint_review_enabled" {{ old('annual_stage_joint_review_enabled', $performanceSettings->annual_stage_joint_review_enabled ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="annual_stage_joint_review_enabled">Enable joint review stage</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="annual_stage_employee_ack_required" value="1" id="annual_stage_employee_ack_required" {{ old('annual_stage_employee_ack_required', $performanceSettings->annual_stage_employee_ack_required ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="annual_stage_employee_ack_required">Require employee acknowledgment stage</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="annual_allow_admin_manual_progress" value="1" id="annual_allow_admin_manual_progress" {{ old('annual_allow_admin_manual_progress', $performanceSettings->annual_allow_admin_manual_progress ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="annual_allow_admin_manual_progress">Allow admin manual progression</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary">Save Performance Settings</button>
                </div>
            </form>
        </div>
    </div>
@endsection
