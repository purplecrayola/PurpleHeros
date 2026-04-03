<?php

namespace App\Http\Controllers;

use App\Models\CompanySettings;
use App\Models\PerformanceReviewSetting;
use App\Support\MailSettingsManager;
use App\Support\MediaStorageManager;
use App\Models\roleTypeUser;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** company/settings/page */
    public function companySettings()
    {
        $this->ensureSettingsAccess();

        $companySettings = CompanySettings::current();

        return view('settings.companysettings', compact('companySettings'));
    }

    /** save record company settings */
    public function saveRecord(Request $request): RedirectResponse
    {
        $this->ensureSettingsAccess();

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state_province' => 'required|string|max:255',
            'postal_code' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:50',
            'mobile_number' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'website_url' => 'nullable|url|max:255',
            'storage_provider' => 'required|in:local,cloudinary',
            'cloudinary_cloud_name' => 'nullable|string|max:255|required_if:storage_provider,cloudinary',
            'cloudinary_api_key' => 'nullable|string|max:255|required_if:storage_provider,cloudinary',
            'cloudinary_api_secret' => 'nullable|string|max:255|required_if:storage_provider,cloudinary',
            'cloudinary_folder' => 'nullable|string|max:255',
            'cloudinary_secure_delivery' => 'nullable|boolean',
            'header_logo_path' => 'nullable|string|max:255',
            'login_logo_path' => 'nullable|string|max:255',
            'favicon_path' => 'nullable|string|max:255',
            'login_image_path' => 'nullable|string|max:255',
            'brand_primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_dark_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_neutral_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'header_text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sidebar_text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sidebar_muted_text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'workflow_current_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'workflow_completed_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'workflow_pending_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'login_page_title' => 'required|string|max:255',
            'login_brand_label' => 'required|string|max:255',
            'login_hero_title' => 'required|string|max:255',
            'login_hero_copy' => 'required|string|max:500',
            'login_left_label' => 'required|string|max:255',
            'login_left_copy' => 'required|string|max:500',
            'login_right_label' => 'required|string|max:255',
            'login_right_copy' => 'required|string|max:500',
            'login_help_line_one' => 'required|string|max:255',
            'login_help_line_two' => 'required|string|max:255',
            'login_help_line_three' => 'required|string|max:255',
            'opay_enabled' => 'nullable|boolean',
            'opay_sandbox_mode' => 'nullable|boolean',
            'opay_base_url' => 'nullable|url|max:255',
            'opay_transfer_path' => 'nullable|string|max:255',
            'opay_merchant_id' => 'nullable|string|max:255',
            'opay_public_key' => 'nullable|string|max:255',
            'opay_secret_key' => 'nullable|string|max:255',
            'kuda_enabled' => 'nullable|boolean',
            'kuda_sandbox_mode' => 'nullable|boolean',
            'kuda_base_url' => 'nullable|url|max:255',
            'kuda_transfer_path' => 'nullable|string|max:255',
            'kuda_api_key' => 'nullable|string|max:255',
            'kuda_secret_key' => 'nullable|string|max:255',
            'kuda_client_email' => 'nullable|email|max:255',
            'header_logo_file' => 'nullable|image|max:5120',
            'login_logo_file' => 'nullable|image|max:5120',
            'favicon_file' => 'nullable|mimes:ico,png,jpg,jpeg,webp|max:2048',
            'login_image_file' => 'nullable|image|max:8192',
        ]);

        DB::beginTransaction();

        try {
            $companySettings = CompanySettings::current();

            if ($request->hasFile('header_logo_file')) {
                $validated['header_logo_path'] = $this->storeBrandAsset($request->file('header_logo_file'), 'header-logo');
            } elseif (empty($validated['header_logo_path'])) {
                $validated['header_logo_path'] = $companySettings->header_logo_path;
            }

            if ($request->hasFile('login_logo_file')) {
                $validated['login_logo_path'] = $this->storeBrandAsset($request->file('login_logo_file'), 'login-logo');
            } elseif (empty($validated['login_logo_path'])) {
                $validated['login_logo_path'] = $companySettings->login_logo_path;
            }

            if ($request->hasFile('favicon_file')) {
                $validated['favicon_path'] = $this->storeBrandAsset($request->file('favicon_file'), 'favicon');
            } elseif (empty($validated['favicon_path'])) {
                $validated['favicon_path'] = $companySettings->favicon_path;
            }

            if ($request->hasFile('login_image_file')) {
                $validated['login_image_path'] = $this->storeBrandAsset($request->file('login_image_file'), 'login-image');
            } elseif (empty($validated['login_image_path'])) {
                $validated['login_image_path'] = $companySettings->login_image_path;
            }

            $validated['cloudinary_secure_delivery'] = (bool) $request->boolean('cloudinary_secure_delivery', true);
            $validated['cloudinary_folder'] = trim((string) ($validated['cloudinary_folder'] ?? '')) ?: 'purple-hr';
            $validated['opay_enabled'] = (bool) $request->boolean('opay_enabled');
            $validated['opay_sandbox_mode'] = (bool) $request->boolean('opay_sandbox_mode', true);
            $validated['kuda_enabled'] = (bool) $request->boolean('kuda_enabled');
            $validated['kuda_sandbox_mode'] = (bool) $request->boolean('kuda_sandbox_mode', true);

            unset($validated['header_logo_file'], $validated['login_logo_file'], $validated['favicon_file'], $validated['login_image_file']);

            $companySettings->fill($validated);
            $companySettings->save();

            DB::commit();
            Toastr::success('Company settings updated successfully.', 'Success');

            return redirect()->back();
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);
            Toastr::error('Company settings could not be updated.', 'Error');

            return redirect()->back()->withInput();
        }
    }

    /** Roles & Permissions  */
    public function rolesPermissions()
    {
        $this->ensureSettingsAccess();

        $rolesPermissions = roleTypeUser::query()
            ->leftJoin('users', 'users.role_name', '=', 'role_type_users.role_type')
            ->select(
                'role_type_users.id',
                'role_type_users.role_type',
                DB::raw('COUNT(users.id) as assigned_users')
            )
            ->groupBy('role_type_users.id', 'role_type_users.role_type')
            ->orderBy('role_type_users.role_type')
            ->get();

        $summary = [
            'roles_count' => $rolesPermissions->count(),
            'assigned_users' => $rolesPermissions->sum('assigned_users'),
            'unassigned_roles' => $rolesPermissions->where('assigned_users', 0)->count(),
        ];

        return view('settings.rolespermissions', compact('rolesPermissions', 'summary'));
    }

    /** add role permissions */
    public function addRecord(Request $request): RedirectResponse
    {
        $this->ensureSettingsAccess();

        $validated = $request->validate([
            'roleName' => 'required|string|max:255',
        ]);

        $roleName = trim($validated['roleName']);

        DB::beginTransaction();

        try {
            $exists = roleTypeUser::query()
                ->whereRaw('LOWER(role_type) = ?', [strtolower($roleName)])
                ->exists();

            if ($exists) {
                DB::rollBack();
                Toastr::error('That role already exists.', 'Error');

                return redirect()->back()->withInput();
            }

            roleTypeUser::query()->create([
                'role_type' => $roleName,
            ]);

            DB::commit();
            Toastr::success('Role created successfully.', 'Success');

            return redirect()->back();
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);
            Toastr::error('Role could not be created.', 'Error');

            return redirect()->back()->withInput();
        }
    }

    /** edit roles permissions */
    public function editRolesPermissions(Request $request): RedirectResponse
    {
        $this->ensureSettingsAccess();

        $validated = $request->validate([
            'id' => 'required|integer|exists:role_type_users,id',
            'roleName' => 'required|string|max:255',
        ]);

        $role = roleTypeUser::query()->findOrFail($validated['id']);
        $roleName = trim($validated['roleName']);

        DB::beginTransaction();

        try {
            $duplicate = roleTypeUser::query()
                ->where('id', '!=', $role->id)
                ->whereRaw('LOWER(role_type) = ?', [strtolower($roleName)])
                ->exists();

            if ($duplicate) {
                DB::rollBack();
                Toastr::error('That role already exists.', 'Error');

                return redirect()->back()->withInput();
            }

            $oldRoleName = $role->role_type;
            $role->update(['role_type' => $roleName]);

            DB::table('users')
                ->where('role_name', $oldRoleName)
                ->update(['role_name' => $roleName]);

            DB::commit();
            Toastr::success('Role updated successfully.', 'Success');

            return redirect()->back();
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);
            Toastr::error('Role could not be updated.', 'Error');

            return redirect()->back()->withInput();
        }
    }

    /** delete roles permissions */
    public function deleteRolesPermissions(Request $request): RedirectResponse
    {
        $this->ensureSettingsAccess();

        $validated = $request->validate([
            'id' => 'required|integer|exists:role_type_users,id',
        ]);

        $role = roleTypeUser::query()->findOrFail($validated['id']);
        $assignedUsers = DB::table('users')->where('role_name', $role->role_type)->count();

        if ($assignedUsers > 0) {
            Toastr::error('This role is assigned to existing users and cannot be deleted yet.', 'Error');

            return redirect()->back();
        }

        try {
            $role->delete();
            Toastr::success('Role deleted successfully.', 'Success');

            return redirect()->back();
        } catch (\Throwable $exception) {
            report($exception);
            Toastr::error('Role could not be deleted.', 'Error');

            return redirect()->back();
        }
    }

    /** localization */
    public function localizationIndex()
    {
        $this->ensureSettingsAccess();

        return view('settings.localization');
    }

    /** salary settings */
    public function salarySettingsIndex()
    {
        $this->ensureSettingsAccess();

        return view('settings.salary-settings');
    }

    public function performanceSettingsIndex()
    {
        $this->ensureSettingsAccess();

        $performanceSettings = PerformanceReviewSetting::current();

        return view('settings.performance-settings', compact('performanceSettings'));
    }

    public function savePerformanceSettings(Request $request): RedirectResponse
    {
        $this->ensureSettingsAccess();

        $validated = $request->validate([
            'objective_weight' => 'required|integer|min:0|max:100',
            'values_weight' => 'required|integer|min:0|max:100',
            'allow_employee_objectives' => 'nullable|boolean',
            'allow_manager_objectives' => 'nullable|boolean',
            'monthly_update_due_day' => 'required|integer|min:1|max:31',
            'weekly_update_due_weekday' => 'required|integer|min:1|max:7',
            'annual_section_objectives_enabled' => 'nullable|boolean',
            'annual_section_values_enabled' => 'nullable|boolean',
            'annual_stage_manager_submit_required' => 'nullable|boolean',
            'annual_stage_calibration_enabled' => 'nullable|boolean',
            'annual_stage_joint_review_enabled' => 'nullable|boolean',
            'annual_stage_employee_ack_required' => 'nullable|boolean',
            'annual_allow_admin_manual_progress' => 'nullable|boolean',
            'values_catalog_lines' => 'nullable|string|max:5000',
        ]);

        if (((int) $validated['objective_weight'] + (int) $validated['values_weight']) !== 100) {
            return redirect()->back()
                ->withErrors(['objective_weight' => 'Objectives weight plus Values weight must equal 100.'])
                ->withInput();
        }

        $valueLines = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['values_catalog_lines'] ?? '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
        if ($valueLines === []) {
            $valueLines = ['Integrity', 'Collaboration', 'Ownership', 'Innovation', 'Customer Focus'];
        }

        $settings = PerformanceReviewSetting::current();
        $settings->update([
            'objective_weight' => (int) $validated['objective_weight'],
            'values_weight' => (int) $validated['values_weight'],
            'allow_employee_objectives' => (bool) $request->boolean('allow_employee_objectives'),
            'allow_manager_objectives' => (bool) $request->boolean('allow_manager_objectives'),
            'monthly_update_due_day' => (int) $validated['monthly_update_due_day'],
            'weekly_update_due_weekday' => (int) $validated['weekly_update_due_weekday'],
            'annual_section_objectives_enabled' => (bool) $request->boolean('annual_section_objectives_enabled'),
            'annual_section_values_enabled' => (bool) $request->boolean('annual_section_values_enabled'),
            'annual_stage_manager_submit_required' => (bool) $request->boolean('annual_stage_manager_submit_required'),
            'annual_stage_calibration_enabled' => (bool) $request->boolean('annual_stage_calibration_enabled'),
            'annual_stage_joint_review_enabled' => (bool) $request->boolean('annual_stage_joint_review_enabled'),
            'annual_stage_employee_ack_required' => (bool) $request->boolean('annual_stage_employee_ack_required'),
            'annual_allow_admin_manual_progress' => (bool) $request->boolean('annual_allow_admin_manual_progress'),
            'values_catalog_json' => json_encode($valueLines),
        ]);

        Toastr::success('Performance settings updated.', 'Success');
        return redirect()->back();
    }

    /** email Settings */
    public function emailSettingsIndex()
    {
        $this->ensureSettingsAccess();

        $companySettings = CompanySettings::current();

        return view('settings.email-settings', compact('companySettings'));
    }

    /** save email delivery settings */
    public function saveEmailSettings(Request $request): RedirectResponse
    {
        $this->ensureSettingsAccess();

        $validated = $request->validate([
            'mail_mailer' => 'required|in:log,smtp,ses',
            'ses_enabled' => 'nullable|boolean',
            'ses_region' => 'nullable|string|max:100|required_if:ses_enabled,1',
            'ses_access_key_id' => 'nullable|string|max:255|required_if:ses_enabled,1',
            'ses_secret_access_key' => 'nullable|string|max:255|required_if:ses_enabled,1',
            'ses_configuration_set' => 'nullable|string|max:255',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
            'mail_reply_to_address' => 'nullable|email|max:255',
        ]);

        DB::beginTransaction();

        try {
            $companySettings = CompanySettings::current();
            $validated['ses_enabled'] = (bool) $request->boolean('ses_enabled');

            if ($validated['ses_enabled']) {
                $validated['mail_mailer'] = 'ses';
            }

            $companySettings->update($validated);
            MailSettingsManager::apply($companySettings->refresh());

            DB::commit();
            Toastr::success('Email delivery settings updated successfully.', 'Success');

            return redirect()->back();
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);
            Toastr::error('Email delivery settings could not be saved.', 'Error');

            return redirect()->back()->withInput();
        }
    }

    /** send test email */
    public function sendEmailSettingsTest(Request $request): RedirectResponse
    {
        $this->ensureSettingsAccess();

        $validated = $request->validate([
            'test_recipient' => 'required|email|max:255',
        ]);

        try {
            $companySettings = CompanySettings::current();
            MailSettingsManager::apply($companySettings);

            Mail::raw(
                'This is a test email from Purple HR mail delivery settings. If you received this, mail transport is working.',
                function ($message) use ($validated, $companySettings): void {
                    $message->to($validated['test_recipient'])
                        ->subject('Purple HR Mail Test');

                    if (filled($companySettings->mail_reply_to_address)) {
                        $message->replyTo((string) $companySettings->mail_reply_to_address);
                    }
                }
            );

            Toastr::success('Test email sent successfully.', 'Success');
        } catch (\Throwable $exception) {
            report($exception);
            Toastr::error('Test email failed. Please review SES credentials, region, and sender configuration.', 'Error');
        }

        return redirect()->back();
    }

    private function ensureSettingsAccess(): void
    {
        $role = Auth::user()?->role_name;
        $allowedRoles = ['Admin', 'Super Admin'];

        abort_unless(in_array($role, $allowedRoles, true), 403);
    }

    private function storeBrandAsset($file, string $prefix): string
    {
        $stored = MediaStorageManager::storeUploadedFile(
            $file,
            'assets/img/brand/uploads',
            $prefix,
            CompanySettings::current(),
        );

        return $stored['path'];
    }
}
