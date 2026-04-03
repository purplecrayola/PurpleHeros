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
    Route::redirect(
        'company/settings/page',
        config('legacy_admin_cutover.legacy_to_filament.company/settings/page', '/admin/company-settings')
    )->middleware('auth')->name('company/settings/page');
    Route::redirect(
        'roles/permissions/page',
        config('legacy_admin_cutover.legacy_to_filament.roles/permissions/page', '/admin/roles-permissions')
    )->middleware('auth')->name('roles/permissions/page');
    Route::redirect(
        'localization/page',
        config('legacy_admin_cutover.legacy_to_filament.localization/page', '/admin/company-settings')
    )->middleware('auth')->name('localization/page');
    Route::redirect(
        'salary/settings/page',
        config('legacy_admin_cutover.legacy_to_filament.salary/settings/page', '/admin/payroll-defaults')
    )->middleware('auth')->name('salary/settings/page');
    Route::redirect(
        'performance/settings/page',
        config('legacy_admin_cutover.legacy_to_filament.performance/settings/page', '/admin/performance-settings')
    )->middleware('auth')->name('performance/settings/page');
    Route::redirect(
        'email/settings/page',
        config('legacy_admin_cutover.legacy_to_filament.email/settings/page', '/admin/email-settings')
    )->middleware('auth')->name('email/settings/page');

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
        Route::redirect(
            'all/employee/card',
            config('legacy_admin_cutover.legacy_to_filament.all/employee/card', '/admin/employees')
        )->middleware('auth')->name('all/employee/card');
        Route::redirect(
            'all/employee/list',
            config('legacy_admin_cutover.legacy_to_filament.all/employee/list', '/admin/employees')
        )->middleware('auth')->name('all/employee/list');
        Route::redirect(
            'form/departments/page',
            config('legacy_admin_cutover.legacy_to_filament.form/departments/page', '/admin/departments')
        )->middleware('auth')->name('form/departments/page');
        Route::redirect(
            'form/designations/page',
            config('legacy_admin_cutover.legacy_to_filament.form/designations/page', '/admin/position-types')
        )->middleware('auth')->name('form/designations/page');
        Route::get('employee/timesheets', 'timeSheetIndex')->middleware('auth')->name('employee/timesheets');
        Route::get('employee/overtime', 'overTimeIndex')->middleware('auth')->name('employee/overtime');
        Route::post('form/timesheet/save', 'saveRecordTimeSheets')->middleware('auth')->name('form/timesheet/save');    
        Route::post('form/timesheet/update', 'updateRecordTimeSheets')->middleware('auth')->name('form/timesheet/update');    
        Route::post('form/timesheet/delete', 'deleteRecordTimeSheets')->middleware('auth')->name('form/timesheet/delete');
        Route::post('form/overtime/save', 'saveRecordOverTime')->middleware('auth')->name('form/overtime/save');    
        Route::post('form/overtime/update', 'updateRecordOverTime')->middleware('auth')->name('form/overtime/update');    
        Route::post('form/overtime/delete', 'deleteRecordOverTime')->middleware('auth')->name('form/overtime/delete');  
    });

    Route::get('form/timesheet/page', function () {
        if (auth()->user()?->isAdmin()) {
            return redirect(config('legacy_admin_cutover.legacy_to_filament.form/timesheet/page', '/admin/timesheet-entries'));
        }

        return redirect()->route('employee/timesheets');
    })->middleware('auth')->name('form/timesheet/page');

    Route::get('form/overtime/page', function () {
        if (auth()->user()?->isAdmin()) {
            return redirect(config('legacy_admin_cutover.legacy_to_filament.form/overtime/page', '/admin/overtime-entries'));
        }

        return redirect()->route('employee/overtime');
    })->middleware('auth')->name('form/overtime/page');

    // ------------------------- profile employee --------------------------//
    Route::controller(EmployeeController::class)->group(function () {
        Route::get('employee/profile/{user_id}', 'profileEmployee')->middleware('auth');
    });

    // --------------------------- form holiday ---------------------------//
    Route::controller(HolidayController::class)->group(function () {
        Route::get('employee/holidays', 'holiday')->middleware('auth')->name('employee/holidays');
        Route::post('form/holidays/save', 'saveRecord')->middleware('auth')->name('form/holidays/save');
        Route::post('form/holidays/update', 'updateRecord')->middleware('auth')->name('form/holidays/update');    
        Route::post('form/holidays/delete', 'deleteRecord')->middleware('auth')->name('form/holidays/delete');
    });

    Route::get('form/holidays/new', function () {
        if (auth()->user()?->isAdmin()) {
            return redirect(config('legacy_admin_cutover.legacy_to_filament.form/holidays/new', '/admin/holidays'));
        }

        return redirect()->route('employee/holidays');
    })->middleware('auth')->name('form/holidays/new');

    // -------------------------- form leaves ----------------------------//
    Route::controller(LeavesController::class)->group(function () {
        Route::redirect(
            'form/leaves/new',
            config('legacy_admin_cutover.legacy_to_filament.form/leaves/new', '/admin/leaves-admins')
        )->middleware('auth')->name('form/leaves/new');
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
        Route::redirect(
            'attendance/page',
            config('legacy_admin_cutover.legacy_to_filament.attendance/page', '/admin/attendance-records')
        )->middleware('auth')->name('attendance/page');
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
    Route::redirect(
        'form/salary/page',
        config('legacy_admin_cutover.legacy_to_filament.form/salary/page', '/admin/staff-salaries')
    )->middleware('auth')->name('form/salary/page');
    Route::redirect(
        'form/payroll/items',
        config('legacy_admin_cutover.legacy_to_filament.form/payroll/items', '/admin/payroll-policy-sets')
    )->middleware('auth')->name('form/payroll/items');

    Route::controller(PayslipPortalController::class)->group(function () {
        Route::get('my/payslips', 'index')->middleware('auth')->name('my/payslips');
        Route::get('my/payslips/{payslip}/download', 'download')->middleware('auth')->name('my/payslips/download');
    });

    Route::controller(EmployeeSignatureController::class)->group(function () {
        Route::get('signature/request/{token}', 'show')->name('signature/request/show');
        Route::post('signature/request/{token}', 'submit')->name('signature/request/submit');
        Route::get('signature/request/{token}/download-signed', 'downloadSigned')->name('signature/request/download-signed');
    });

    Route::controller(ReferenceCheckController::class)->group(function () {
        Route::get('reference/check/{token}', 'show')->name('reference/check/show');
        Route::post('reference/check/{token}', 'submit')->name('reference/check/submit');
    });

    // --------------------------- learning catalog  ----------------------------//
    Route::controller(LearningCatalogController::class)->group(function () {
        Route::get('learning/catalog', 'index')->middleware('auth')->name('learning/catalog');
        Route::get('learning/course/{id}', 'viewCourse')->middleware('auth')->name('learning/course/view');
        Route::post('learning/course/{id}/start', 'startCourse')->middleware('auth')->name('learning/course/start');
        Route::post('learning/enrollment/{id}/progress', 'recordProgress')->middleware('auth')->name('learning/enrollment/progress');
        Route::post('learning/enrollment/{id}/bookmark', 'addBookmark')->middleware('auth')->name('learning/enrollment/bookmark');
        Route::post('learning/enrollment/{id}/telemetry', 'telemetry')->middleware('auth')->name('learning/enrollment/telemetry');
    });

    // ---------------------------- reports  ----------------------------//
    Route::redirect(
        'form/expense/reports/page',
        config('legacy_admin_cutover.legacy_to_filament.form/expense/reports/page', '/admin/reports-hub')
    )->middleware('auth')->name('form/expense/reports/page');
    Route::redirect(
        'form/invoice/reports/page',
        config('legacy_admin_cutover.legacy_to_filament.form/invoice/reports/page', '/admin/reports-hub')
    )->middleware('auth')->name('form/invoice/reports/page');
    Route::redirect(
        'form/daily/reports/page',
        config('legacy_admin_cutover.legacy_to_filament.form/daily/reports/page', '/admin/reports-hub')
    )->middleware('auth')->name('form/daily/reports/page');
    Route::redirect(
        'form/leave/reports/page',
        config('legacy_admin_cutover.legacy_to_filament.form/leave/reports/page', '/admin/reports-hub')
    )->middleware('auth')->name('form/leave/reports/page');
    Route::redirect(
        'form/payments/reports/page',
        config('legacy_admin_cutover.legacy_to_filament.form/payments/reports/page', '/admin/reports-hub')
    )->middleware('auth')->name('form/payments/reports/page');
    Route::redirect(
        'form/employee/reports/page',
        config('legacy_admin_cutover.legacy_to_filament.form/employee/reports/page', '/admin/reports-hub')
    )->middleware('auth')->name('form/employee/reports/page');

    // --------------------------- performance  -------------------------//
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

    // --------------------------- performance tracker  -------------------------//
    Route::controller(PerformanceTrackerController::class)->group(function () {
        Route::get('performance/tracker', 'myTracker')->middleware('auth')->name('performance/tracker');
        Route::post('performance/tracker/goal/save', 'saveGoal')->middleware('auth')->name('performance/tracker/goal/save');
        Route::post('performance/tracker/goal/{id}/submit', 'submitGoalUpdate')->middleware('auth')->name('performance/tracker/goal/submit');
        Route::post('performance/tracker/objective/save', 'saveObjective')->middleware('auth')->name('performance/tracker/objective/save');
        Route::get('performance/annual/review', 'annualReview')->middleware('auth')->name('performance/annual/review');
        Route::get('performance/annual/review/{year}/download', 'downloadAnnualReview')->middleware('auth')->name('performance/annual/review/download');
        Route::post('performance/annual/review/{year}/self-save', 'saveSelfAnnualReview')->middleware('auth')->name('performance/annual/review/self-save');
        Route::post('performance/annual/review/{year}/acknowledge', 'acknowledgeAnnualReview')->middleware('auth')->name('performance/annual/review/acknowledge');
        Route::get('performance/team/annual-reviews', 'teamAnnualReviews')->middleware('auth')->name('performance/team/annual-reviews');
        Route::post('performance/team/annual-reviews/generate', 'generateAnnualReviews')->middleware('auth')->name('performance/team/annual-reviews/generate');
        Route::match(['get', 'post'], 'performance/team/annual-reviews/{id}', 'managerAnnualReview')->middleware('auth')->name('performance/team/annual-reviews/view');
        Route::get('performance/team/annual-reviews/{id}/download', 'downloadAnnualReviewById')->middleware('auth')->name('performance/team/annual-reviews/download');
        Route::post('performance/team/annual-reviews/{id}/progress', 'adminProgressAnnualReview')->middleware('auth')->name('performance/team/annual-reviews/progress');
        Route::get('performance/team/reviews', 'teamReviews')->middleware('auth')->name('performance/team/reviews');
        Route::post('performance/team/reviews/{id}', 'reviewGoal')->middleware('auth')->name('performance/team/reviews/save');
    });

    // --------------------------- training  ----------------------------//
    Route::redirect(
        'form/training/list/page',
        config('legacy_admin_cutover.legacy_to_filament.form/training/list/page', '/admin/trainings')
    )->middleware('auth')->name('form/training/list/page');

    // --------------------------- trainers  ----------------------------//
    Route::redirect(
        'form/trainers/list/page',
        config('legacy_admin_cutover.legacy_to_filament.form/trainers/list/page', '/admin/trainers')
    )->middleware('auth')->name('form/trainers/list/page');

    // ------------------------- training type  -------------------------//
    Route::redirect(
        'form/training/type/list/page',
        config('legacy_admin_cutover.legacy_to_filament.form/training/type/list/page', '/admin/training-types')
    )->middleware('auth')->name('form/training/type/list/page');

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
