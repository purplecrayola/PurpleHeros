<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/** for side bar menu active */
if (! function_exists('set_active')) {
    function set_active($route) {
        if (is_array($route )){
            return in_array(Request::path(), $route) ? 'active' : '';
        }
        return Request::path() == $route ? 'active' : '';
    }
}

Route::get('/', function () {
    return view('auth.login');
});

Route::group(['middleware'=>'auth'],function()
{
    Route::get('home',function()
    {
        return view('home');
    });
    Route::get('home',function()
    {
        return view('home');
    });
});

Auth::routes();

Route::group(['namespace' => 'App\Http\Controllers\Auth'],function()
{
    // -----------------------------login----------------------------------------//
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'login')->name('login');
        Route::post('/login', 'authenticate');
        Route::post('/logout', 'logout')->name('logout');
    });

    // ------------------------------ register ----------------------------------//
    Route::controller(RegisterController::class)->group(function () {
        Route::get('/register', 'register')->name('register');
        Route::post('/register','storeUser')->name('register');    
    });

    // ----------------------------- forget password ----------------------------//
    Route::controller(ForgotPasswordController::class)->group(function () {
        Route::get('forget-password', 'getEmail')->name('forget-password');
        Route::post('forget-password', 'postEmail')->name('forget-password');    
    });

    // ----------------------------- reset password -----------------------------//
    Route::controller(ResetPasswordController::class)->group(function () {
        Route::get('reset-password/{token}', 'getPassword');
        Route::post('reset-password', 'updatePassword');    
    });
});

Route::group(['namespace' => 'App\Http\Controllers'],function()
{
    // ----------------------------- main dashboard ------------------------------//
    Route::controller(HomeController::class)->group(function () {
        Route::get('/home', 'index')->name('home');
        Route::get('em/dashboard', 'emDashboard')->name('em/dashboard');
        Route::get('search', 'globalSearch')->middleware('auth')->name('global/search');
    });

    // ----------------------------- lock screen --------------------------------//
    Route::controller(LockScreen::class)->group(function () {
        Route::get('lock_screen','lockScreen')->middleware('auth')->name('lock_screen');
        Route::post('unlock', 'unlock')->name('unlock');    
    });

    // -----------------------------settings-------------------------------------//
    Route::controller(SettingController::class)->group(function () {
        Route::get('company/settings/page', 'companySettings')->middleware('auth')->name('company/settings/page'); /** index page */
        Route::post('company/settings/save', 'saveRecord')->middleware('auth')->name('company/settings/save'); /** save record or update */
        Route::get('roles/permissions/page', 'rolesPermissions')->middleware('auth')->name('roles/permissions/page');
        Route::post('roles/permissions/save', 'addRecord')->middleware('auth')->name('roles/permissions/save');
        Route::post('roles/permissions/update', 'editRolesPermissions')->middleware('auth')->name('roles/permissions/update');
        Route::post('roles/permissions/delete', 'deleteRolesPermissions')->middleware('auth')->name('roles/permissions/delete');
        Route::redirect(
            'localization/page',
            config('legacy_admin_cutover.legacy_to_filament.localization/page', '/admin/company-settings')
        )->middleware('auth')->name('localization/page');
        Route::redirect(
            'salary/settings/page',
            config('legacy_admin_cutover.legacy_to_filament.salary/settings/page', '/admin/payroll-defaults')
        )->middleware('auth')->name('salary/settings/page');
        Route::get('performance/settings/page', 'performanceSettingsIndex')->middleware('auth')->name('performance/settings/page');
        Route::post('performance/settings/save', 'savePerformanceSettings')->middleware('auth')->name('performance/settings/save');
        Route::redirect(
            'email/settings/page',
            config('legacy_admin_cutover.legacy_to_filament.email/settings/page', '/admin/email-settings')
        )->middleware('auth')->name('email/settings/page');
        Route::post('email/settings/save', 'saveEmailSettings')->middleware('auth')->name('email/settings/save');
        Route::post('email/settings/test', 'sendEmailSettingsTest')->middleware('auth')->name('email/settings/test');
    });

    // ----------------------------- manage users -------d-----------------------//
    Route::controller(UserManagementController::class)->group(function () {
        Route::get('profile_user', 'profile')->middleware('auth')->name('profile_user');
        Route::post('profile/information/save', 'profileInformation')->name('profile/information/save');
        Route::post('profile/bank-statutory/save', 'saveBankStatutory')->name('profile/bank-statutory/save');
        Route::post('profile/onboarding/save', 'saveOnboardingData')->name('profile/onboarding/save');
        Route::get('userManagement', 'index')->middleware('auth')->name('userManagement');
        Route::post('user/add/save', 'addNewUserSave')->name('user/add/save');
        Route::post('update', 'update')->name('update');
        Route::post('user/delete', 'delete')->middleware('auth')->name('user/delete');
        
        Route::post('user/profile/emergency/contact/save', 'emergencyContactSaveOrUpdate')->name('user/profile/emergency/contact/save'); /** save or update emergency contact */
        Route::get('get-users-data', 'getUsersData')->name('get-users-data'); /** get all data users */
        
    });

    // ----------------------------- account security ------------------------------//
    Route::controller(AccountSecurityController::class)->group(function () {
        Route::get('change/password', 'showChangePassword')->middleware('auth')->name('change/password');
        Route::post('change/password/db', 'updatePassword')->middleware('auth')->name('change/password/db');
    });

    // --------------------------------- job ---------------------------------//
    Route::controller(JobController::class)->group(function () {
        Route::get('form/job/list','jobList')->name('form/job/list');
        Route::get('form/job/view/{id}', 'jobView');
        Route::get('user/dashboard/index', 'userDashboard')->middleware('auth')->name('user/dashboard/index');    
        Route::get('jobs/dashboard/index', 'jobsDashboard')->middleware('auth')->name('jobs/dashboard/index');    
        Route::get('user/dashboard/all', 'userDashboardAll')->middleware('auth')->name('user/dashboard/all');    
        Route::get('user/dashboard/save', 'userDashboardSave')->middleware('auth')->name('user/dashboard/save');
        Route::get('user/dashboard/applied/jobs', 'userDashboardApplied')->middleware('auth')->name('user/dashboard/applied/jobs');
        Route::get('user/dashboard/interviewing', 'userDashboardInterviewing')->middleware('auth')->name('user/dashboard/interviewing');
        Route::get('user/dashboard/offered/jobs', 'userDashboardOffered')->middleware('auth')->name('user/dashboard/offered/jobs');
        Route::get('user/dashboard/visited/jobs', 'userDashboardVisited')->middleware('auth')->name('user/dashboard/visited/jobs');
        Route::get('user/dashboard/archived/jobs', 'userDashboardArchived')->middleware('auth')->name('user/dashboard/archived/jobs');
        Route::get('jobs', 'Jobs')->middleware('auth')->name('jobs');
        Route::get('job/applicants/{job_title}', 'jobApplicants')->middleware('auth');
        Route::get('job/details/{id}', 'jobDetails')->middleware('auth');
        Route::get('cv/download/{id}', 'downloadCV')->middleware('auth');
        
        Route::post('form/jobs/save', 'JobsSaveRecord')->name('form/jobs/save');
        Route::post('form/apply/job/save', 'applyJobSaveRecord')->name('form/apply/job/save');
        Route::post('form/apply/job/update', 'applyJobUpdateRecord')->name('form/apply/job/update');

        Route::get('page/manage/resumes', 'manageResumesIndex')->middleware('auth')->name('page/manage/resumes');
        Route::get('page/shortlist/candidates', 'shortlistCandidatesIndex')->middleware('auth')->name('page/shortlist/candidates');
        Route::get('page/interview/questions', 'interviewQuestionsIndex')->middleware('auth')->name('page/interview/questions'); // view page
        Route::post('save/category', 'categorySave')->name('save/category'); // save record category
        Route::post('save/questions', 'questionSave')->name('save/questions'); // save record questions
        Route::post('questions/update', 'questionsUpdate')->name('questions/update'); // update question
        Route::post('questions/delete', 'questionsDelete')->middleware('auth')->name('questions/delete'); // delete question
        Route::get('page/offer/approvals', 'offerApprovalsIndex')->middleware('auth')->name('page/offer/approvals');
        Route::get('page/experience/level', 'experienceLevelIndex')->middleware('auth')->name('page/experience/level');
        Route::get('page/candidates', 'candidatesIndex')->middleware('auth')->name('page/candidates');
        Route::get('page/schedule/timing', 'scheduleTimingIndex')->middleware('auth')->name('page/schedule/timing');
        Route::get('page/aptitude/result', 'aptituderesultIndex')->middleware('auth')->name('page/aptitude/result');

        Route::post('jobtypestatus/update', 'jobTypeStatusUpdate')->name('jobtypestatus/update'); // update status job type ajax

    });
    
    // ---------------------------- form employee ---------------------------//
    Route::controller(EmployeeController::class)->group(function () {
        Route::get('all/employee/card', 'cardAllEmployee')->middleware('auth')->name('all/employee/card');
        Route::get('all/employee/list', 'listAllEmployee')->middleware('auth')->name('all/employee/list');
        Route::post('all/employee/save', 'saveRecord')->middleware('auth')->name('all/employee/save');
        Route::get('all/employee/view/edit/{employee_id}', 'viewRecord');
        Route::post('all/employee/update', 'updateRecord')->middleware('auth')->name('all/employee/update');
        Route::post('all/employee/delete/{employee_id}', 'deleteRecord')->middleware('auth')->name('all/employee/delete');
        Route::post('all/employee/search', 'employeeSearch')->name('all/employee/search');
        Route::post('all/employee/list/search', 'employeeListSearch')->name('all/employee/list/search');

        Route::get('form/departments/page', 'index')->middleware('auth')->name('form/departments/page');    
        Route::post('form/departments/save', 'saveRecordDepartment')->middleware('auth')->name('form/departments/save');    
        Route::post('form/department/update', 'updateRecordDepartment')->middleware('auth')->name('form/department/update');    
        Route::post('form/department/delete', 'deleteRecordDepartment')->middleware('auth')->name('form/department/delete');  
        
        Route::get('form/designations/page', 'designationsIndex')->middleware('auth')->name('form/designations/page');    
        Route::post('form/designations/save', 'saveRecordDesignations')->middleware('auth')->name('form/designations/save');    
        Route::post('form/designations/update', 'updateRecordDesignations')->middleware('auth')->name('form/designations/update');    
        Route::post('form/designations/delete', 'deleteRecordDesignations')->middleware('auth')->name('form/designations/delete');
        
        Route::get('form/timesheet/page', 'timeSheetIndex')->middleware('auth')->name('form/timesheet/page');    
        Route::post('form/timesheet/save', 'saveRecordTimeSheets')->middleware('auth')->name('form/timesheet/save');    
        Route::post('form/timesheet/update', 'updateRecordTimeSheets')->middleware('auth')->name('form/timesheet/update');    
        Route::post('form/timesheet/delete', 'deleteRecordTimeSheets')->middleware('auth')->name('form/timesheet/delete');
        
        Route::get('form/overtime/page', 'overTimeIndex')->middleware('auth')->name('form/overtime/page');    
        Route::post('form/overtime/save', 'saveRecordOverTime')->middleware('auth')->name('form/overtime/save');    
        Route::post('form/overtime/update', 'updateRecordOverTime')->middleware('auth')->name('form/overtime/update');    
        Route::post('form/overtime/delete', 'deleteRecordOverTime')->middleware('auth')->name('form/overtime/delete');  
    });

    // ------------------------- profile employee --------------------------//
    Route::controller(EmployeeController::class)->group(function () {
        Route::get('employee/profile/{user_id}', 'profileEmployee')->middleware('auth');
    });

    // --------------------------- form holiday ---------------------------//
    Route::controller(HolidayController::class)->group(function () {
        Route::get('form/holidays/new', 'holiday')->middleware('auth')->name('form/holidays/new');
        Route::post('form/holidays/save', 'saveRecord')->middleware('auth')->name('form/holidays/save');
        Route::post('form/holidays/update', 'updateRecord')->middleware('auth')->name('form/holidays/update');    
        Route::post('form/holidays/delete', 'deleteRecord')->middleware('auth')->name('form/holidays/delete');
    });

    // -------------------------- form leaves ----------------------------//
    Route::controller(LeavesController::class)->group(function () {
        Route::get('form/leaves/new', 'leaves')->middleware('auth')->name('form/leaves/new');
        Route::get('form/leavesemployee/new', 'leavesEmployee')->middleware('auth')->name('form/leavesemployee/new');
        Route::post('form/leaves/save', 'saveRecord')->middleware('auth')->name('form/leaves/save');
        Route::post('form/leaves/edit', 'editRecordLeave')->middleware('auth')->name('form/leaves/edit');
        Route::post('form/leaves/edit/delete','deleteLeave')->middleware('auth')->name('form/leaves/edit/delete');    
    });

    // ------------------------ form attendance  -------------------------//
    Route::controller(LeavesController::class)->group(function () {
        Route::redirect(
            'form/leavesettings/page',
            config('legacy_admin_cutover.legacy_to_filament.form/leavesettings/page', '/admin/leave-settings')
        )->middleware('auth')->name('form/leavesettings/page');
        Route::get('attendance/page', 'attendanceIndex')->middleware('auth')->name('attendance/page');
        Route::get('attendance/employee/page', 'AttendanceEmployee')->middleware('auth')->name('attendance/employee/page');
        Route::post('attendance/employee/check-in', 'attendanceCheckIn')->middleware('auth')->name('attendance/employee/check-in');
        Route::post('attendance/employee/check-out', 'attendanceCheckOut')->middleware('auth')->name('attendance/employee/check-out');
        Route::redirect(
            'form/shiftscheduling/page',
            config('legacy_admin_cutover.legacy_to_filament.form/shiftscheduling/page', '/admin/shift-scheduling')
        )->middleware('auth')->name('form/shiftscheduling/page');
        Route::redirect(
            'form/shiftlist/page',
            config('legacy_admin_cutover.legacy_to_filament.form/shiftlist/page', '/admin/shift-scheduling')
        )->middleware('auth')->name('form/shiftlist/page');
    });

    // ------------------------ form payroll  ----------------------------//
    Route::controller(PayrollController::class)->group(function () {
        Route::get('form/salary/page', 'salary')->middleware('auth')->name('form/salary/page');
        Route::post('form/salary/save','saveRecord')->middleware('auth')->name('form/salary/save');
        Route::post('form/salary/update', 'updateRecord')->middleware('auth')->name('form/salary/update');
        Route::post('form/salary/delete', 'deleteRecord')->middleware('auth')->name('form/salary/delete');
        Route::get('form/salary/view/{user_id}', 'salaryView')->middleware('auth');
        Route::get('form/payroll/items', 'payrollItems')->middleware('auth')->name('form/payroll/items');    
        Route::get('extra/report/pdf', 'reportPDF')->middleware('auth');    
        Route::get('extra/report/excel', 'reportExcel')->middleware('auth');    
    });

    Route::controller(PayslipPortalController::class)->group(function () {
        Route::get('my/payslips', 'index')->middleware('auth')->name('my/payslips');
        Route::get('my/payslips/{payslip}/download', 'download')->middleware('auth')->name('my/payslips/download');
    });

    // ---------------------------- reports  ----------------------------//
    Route::controller(ExpenseReportsController::class)->group(function () {
        Route::redirect(
            'form/expense/reports/page',
            config('legacy_admin_cutover.legacy_to_filament.form/expense/reports/page', '/admin/reports-hub')
        )->middleware('auth')->name('form/expense/reports/page');
        Route::redirect(
            'form/invoice/reports/page',
            config('legacy_admin_cutover.legacy_to_filament.form/invoice/reports/page', '/admin/reports-hub')
        )->middleware('auth')->name('form/invoice/reports/page');
        Route::get('form/daily/reports/page', 'dailyReport')->middleware('auth')->name('form/daily/reports/page');
        Route::get('form/leave/reports/page','leaveReport')->middleware('auth')->name('form/leave/reports/page');
        Route::get('form/payments/reports/page','paymentsReportIndex')->middleware('auth')->name('form/payments/reports/page');
        Route::get('form/employee/reports/page','employeeReportsIndex')->middleware('auth')->name('form/employee/reports/page');
    });

    // --------------------------- performance  -------------------------//
    Route::controller(PerformanceController::class)->group(function () {
        Route::redirect(
            'form/performance/indicator/page',
            config('legacy_admin_cutover.legacy_to_filament.form/performance/indicator/page', '/admin/performance-hub')
        )->middleware('auth')->name('form/performance/indicator/page');
        Route::redirect(
            'form/performance/page',
            config('legacy_admin_cutover.legacy_to_filament.form/performance/page', '/admin/performance-hub')
        )->middleware('auth')->name('form/performance/page');
        Route::redirect(
            'form/performance/appraisal/page',
            config('legacy_admin_cutover.legacy_to_filament.form/performance/appraisal/page', '/admin/performance-hub')
        )->middleware('auth')->name('form/performance/appraisal/page');
        Route::post('form/performance/indicator/save','saveRecordIndicator')->middleware('auth')->name('form/performance/indicator/save');
        Route::post('form/performance/indicator/delete','deleteIndicator')->middleware('auth')->name('form/performance/indicator/delete');
        Route::post('form/performance/indicator/update', 'updateIndicator')->middleware('auth')->name('form/performance/indicator/update');
        Route::post('form/performance/appraisal/save', 'saveRecordAppraisal')->middleware('auth')->name('form/performance/appraisal/save');
        Route::post('form/performance/appraisal/update', 'updateAppraisal')->middleware('auth')->name('form/performance/appraisal/update');
        Route::post('form/performance/appraisal/delete', 'deleteAppraisal')->middleware('auth')->name('form/performance/appraisal/delete');
    });

    // --------------------------- performance tracker  -------------------------//
    Route::controller(PerformanceTrackerController::class)->group(function () {
        Route::get('performance/tracker', 'myTracker')->middleware('auth')->name('performance/tracker');
        Route::post('performance/tracker/goal/save', 'saveGoal')->middleware('auth')->name('performance/tracker/goal/save');
        Route::post('performance/tracker/goal/{id}/submit', 'submitGoalUpdate')->middleware('auth')->name('performance/tracker/goal/submit');
        Route::post('performance/tracker/objective/save', 'saveObjective')->middleware('auth')->name('performance/tracker/objective/save');
        Route::get('performance/annual/review', 'annualReview')->middleware('auth')->name('performance/annual/review');
        Route::get('performance/annual/review/{year}/download', 'downloadAnnualReview')->middleware('auth')->name('performance/annual/review/download');
        Route::post('performance/annual/review/{year}/self-save', 'saveSelfAnnualReview')->middleware('auth')->name('performance/annual/review/self-save');
        Route::get('performance/team/annual-reviews', 'teamAnnualReviews')->middleware('auth')->name('performance/team/annual-reviews');
        Route::post('performance/team/annual-reviews/generate', 'generateAnnualReviews')->middleware('auth')->name('performance/team/annual-reviews/generate');
        Route::match(['get', 'post'], 'performance/team/annual-reviews/{id}', 'managerAnnualReview')->middleware('auth')->name('performance/team/annual-reviews/view');
        Route::get('performance/team/annual-reviews/{id}/download', 'downloadAnnualReviewById')->middleware('auth')->name('performance/team/annual-reviews/download');
        Route::get('performance/team/reviews', 'teamReviews')->middleware('auth')->name('performance/team/reviews');
        Route::post('performance/team/reviews/{id}', 'reviewGoal')->middleware('auth')->name('performance/team/reviews/save');
    });

    // --------------------------- training  ----------------------------//
    Route::controller(TrainingController::class)->group(function () {
        Route::redirect(
            'form/training/list/page',
            config('legacy_admin_cutover.legacy_to_filament.form/training/list/page', '/admin/trainings')
        )->middleware('auth')->name('form/training/list/page');
        Route::post('form/training/save', 'addNewTraining')->middleware('auth')->name('form/training/save');
        Route::post('form/training/delete', 'deleteTraining')->middleware('auth')->name('form/training/delete');
        Route::post('form/training/update', 'updateTraining')->middleware('auth')->name('form/training/update');    
    });

    // --------------------------- trainers  ----------------------------//
    Route::controller(TrainersController::class)->group(function () {
        Route::redirect(
            'form/trainers/list/page',
            config('legacy_admin_cutover.legacy_to_filament.form/trainers/list/page', '/admin/trainers')
        )->middleware('auth')->name('form/trainers/list/page');
        Route::post('form/trainers/save', 'saveRecord')->middleware('auth')->name('form/trainers/save');
        Route::post('form/trainers/update', 'updateRecord')->middleware('auth')->name('form/trainers/update');
        Route::post('form/trainers/delete', 'deleteRecord')->middleware('auth')->name('form/trainers/delete');
    });

    // ------------------------- training type  -------------------------//
    Route::controller(TrainingTypeController::class)->group(function () {
        Route::redirect(
            'form/training/type/list/page',
            config('legacy_admin_cutover.legacy_to_filament.form/training/type/list/page', '/admin/training-types')
        )->middleware('auth')->name('form/training/type/list/page');
        Route::post('form/training/type/save', 'saveRecord')->middleware('auth')->name('form/training/type/save');
        Route::post('form//training/type/update', 'updateRecord')->middleware('auth')->name('form//training/type/update');
        Route::post('form//training/type/delete', 'deleteTrainingType')->middleware('auth')->name('form//training/type/delete');    
    });

    // ----------------------------- sales  ----------------------------//
    Route::controller(SalesController::class)->group(function () {

        // -------------------- estimate  -------------------//
        Route::get('form/estimates/page', 'estimatesIndex')->middleware('auth')->name('form/estimates/page');
        Route::get('create/estimate/page', 'createEstimateIndex')->middleware('auth')->name('create/estimate/page');
        Route::get('edit/estimate/{estimate_number}', 'editEstimateIndex')->middleware('auth');
        Route::get('estimate/view/{estimate_number}', 'viewEstimateIndex')->middleware('auth');

        Route::post('create/estimate/save', 'createEstimateSaveRecord')->middleware('auth')->name('create/estimate/save');
        Route::post('create/estimate/update', 'EstimateUpdateRecord')->middleware('auth')->name('create/estimate/update');
        Route::post('estimate_add/delete', 'EstimateAddDeleteRecord')->middleware('auth')->name('estimate_add/delete');
        Route::post('estimate/delete', 'EstimateDeleteRecord')->middleware('auth')->name('estimate/delete');
        // ---------------------- payments  ---------------//
        Route::get('payments', 'Payments')->middleware('auth')->name('payments');
        Route::get('expenses/page', 'Expenses')->middleware('auth')->name('expenses/page');
        Route::post('expenses/save', 'saveRecord')->middleware('auth')->name('expenses/save');
        Route::post('expenses/update', 'updateRecord')->middleware('auth')->name('expenses/update');
        Route::post('expenses/delete', 'deleteRecord')->middleware('auth')->name('expenses/delete');
            // ---------------------- search expenses  ---------------//
        Route::get('expenses/search', 'searchRecord')->middleware('auth')->name('expenses/search');
        Route::post('expenses/search', 'searchRecord')->middleware('auth')->name('expenses/search');
        
    });

    // ==================== user profile user ===========================//

    // ---------------------- personal information ----------------------//
    Route::controller(PersonalInformationController::class)->group(function () {
        Route::post('user/information/save', 'saveRecord')->middleware('auth')->name('user/information/save');
    });

    // ---------------------- bank information  -----------------------//
    Route::controller(BankInformationController::class)->group(function () {
        Route::post('bank/information/save', 'saveRecord')->middleware('auth')->name('bank/information/save');
    });
});

// ----------------------------- legacy cutover redirects ------------------------------//
// These are intentionally registered after legacy routes so they become the effective match.
Route::middleware('auth')->group(function () {
    if (config('legacy_admin_cutover.enabled', true)) {
        $explicitRedirects = [
            'localization/page',
            'email/settings/page',
            'salary/settings/page',
            'form/expense/reports/page',
            'form/invoice/reports/page',
            'form/performance/indicator/page',
            'form/performance/page',
            'form/performance/appraisal/page',
            'form/training/list/page',
            'form/trainers/list/page',
            'form/training/type/list/page',
            'form/leavesettings/page',
            'form/shiftscheduling/page',
            'form/shiftlist/page',
        ];

        foreach ((array) config('legacy_admin_cutover.legacy_to_filament', []) as $legacyPath => $filamentPath) {
            if (in_array($legacyPath, $explicitRedirects, true)) {
                continue;
            }

            Route::redirect($legacyPath, $filamentPath);
        }
    }
});
