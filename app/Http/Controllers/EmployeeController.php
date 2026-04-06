<?php

namespace App\Http\Controllers;

use App\Models\department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeEducation;
use App\Models\EmployeeExperience;
use App\Models\EmployeeFamilyMember;
use App\Models\EmployeeReference;
use App\Models\EmployeeStatutoryProfile;
use App\Models\module_permission;
use App\Models\OvertimeEntry;
use App\Models\PersonalInformation;
use App\Models\ProfileInformation;
use App\Models\TimesheetEntry;
use App\Models\User;
use App\Models\UserEmergencyContact;
use App\Support\InAppNotifier;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $method = $request->route()?->getActionMethod();
            $selfServiceMethods = [
                'timeSheetIndex',
                'saveRecordTimeSheets',
                'updateRecordTimeSheets',
                'deleteRecordTimeSheets',
                'overTimeIndex',
                'saveRecordOverTime',
                'updateRecordOverTime',
                'deleteRecordOverTime',
            ];

            if ($method === 'profileEmployee') {
                abort_unless($user && $user->canAccessUserId((string) $request->route('user_id')), 403);
                return $next($request);
            }

            if (in_array($method, $selfServiceMethods, true)) {
                abort_unless($user !== null, 403);
                return $next($request);
            }

            abort_unless($user && $user->isAdmin(), 403);

            return $next($request);
        });
    }

    /** all employee card view */
    public function cardAllEmployee(Request $request)
    {
        $users = $this->employeeBaseQuery()->get();
        $userList = $this->availableUserList()->get();
        $permission_lists = DB::table('permission_lists')->get();

        return view('employees.allemployeecard', compact('users', 'userList', 'permission_lists'));
    }

    /** all employee list */
    public function listAllEmployee()
    {
        $users = $this->employeeBaseQuery()->get();
        $userList = $this->availableUserList()->get();
        $permission_lists = DB::table('permission_lists')->get();

        return view('employees.employeelist', compact('users', 'userList', 'permission_lists'));
    }

    /** save data employee */
    public function saveRecord(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email',
            'birthDate' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'employee_id' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $user = User::where('user_id', $request->employee_id)
                ->where('email', $request->email)
                ->first();

            if (! $user) {
                DB::rollBack();
                Toastr::error('Selected user could not be matched to an account :)', 'Error');

                return redirect()->back();
            }

            $employee = Employee::where('employee_id', $request->employee_id)->first();
            if ($employee !== null) {
                DB::rollBack();
                Toastr::error('Add new employee exits :)', 'Error');

                return redirect()->back();
            }

            Employee::create([
                'name' => $request->name,
                'email' => $request->email,
                'birth_date' => $request->birthDate,
                'gender' => $request->gender,
                'employee_id' => $request->employee_id,
                'company' => $request->company,
            ]);

            $permissionRows = $this->buildPermissionRows(
                $request->employee_id,
                $request->permission ?? [],
                $request->id_count ?? [],
                $request->read ?? [],
                $request->write ?? [],
                $request->create ?? [],
                $request->delete ?? [],
                $request->import ?? [],
                $request->export ?? []
            );

            if (! empty($permissionRows)) {
                DB::table('module_permissions')->insert($permissionRows);
            }

            DB::commit();
            Toastr::success('Add new employee successfully :)', 'Success');

            return redirect()->route('all/employee/card');
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error('Add new employee fail :)', 'Error');

            return redirect()->back();
        }
    }

    /** view edit record */
    public function viewRecord($employee_id)
    {
        $employee = DB::table('employees')->where('employee_id', $employee_id)->first();

        if (! $employee) {
            abort(404);
        }

        $permission = DB::table('module_permissions')
            ->where('employee_id', $employee_id)
            ->orderBy('id')
            ->get();

        return view('employees.edit.editemployee', [
            'employee' => $employee,
            'permission' => $permission,
        ]);
    }

    /** update record employee */
    public function updateRecord(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'user_id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email',
            'birth_date' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:255',
            'employee_id' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $updateEmployee = [
                'name' => $request->name,
                'email' => $request->email,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'employee_id' => $request->employee_id,
                'company' => $request->company,
            ];

            $updateUser = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            $permissionRows = $this->buildPermissionRows(
                $request->employee_id,
                $request->permission ?? [],
                $request->id_permission ?? [],
                $request->read ?? [],
                $request->write ?? [],
                $request->create ?? [],
                $request->delete ?? [],
                $request->import ?? [],
                $request->export ?? []
            );

            foreach ($permissionRows as $permissionRow) {
                $permissionId = $permissionRow['id'];
                unset($permissionRow['id']);
                module_permission::where('id', $permissionId)->update($permissionRow);
            }

            User::where('user_id', $request->user_id)->update($updateUser);
            Employee::where('id', $request->id)->update($updateEmployee);

            DB::commit();
            Toastr::success('updated record successfully :)', 'Success');

            return redirect()->route('all/employee/card');
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error('updated record fail :)', 'Error');

            return redirect()->back();
        }
    }

    /** delete record */
    public function deleteRecord($employee_id)
    {
        DB::beginTransaction();

        try {
            Employee::where('employee_id', $employee_id)->delete();
            module_permission::where('employee_id', $employee_id)->delete();

            DB::commit();
            Toastr::success('Delete record successfully :)', 'Success');

            return redirect()->route('all/employee/card');
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error('Delete record fail :)', 'Error');

            return redirect()->back();
        }
    }

    /** employee search */
    public function employeeSearch(Request $request)
    {
        $users = $this->applyEmployeeFilters($request)->get();
        $permission_lists = DB::table('permission_lists')->get();
        $userList = $this->availableUserList()->get();

        return view('employees.allemployeecard', compact('users', 'userList', 'permission_lists'));
    }

    /** list search employee */
    public function employeeListSearch(Request $request)
    {
        $users = $this->applyEmployeeFilters($request)->get();
        $permission_lists = DB::table('permission_lists')->get();
        $userList = $this->availableUserList()->get();

        return view('employees.employeelist', compact('users', 'userList', 'permission_lists'));
    }

    /** employee profile with all controller user */
    public function profileEmployee($user_id)
    {
        $user = DB::table('users')
            ->leftJoin('personal_information as pi', 'pi.user_id', 'users.user_id')
            ->leftJoin('profile_information as pr', 'pr.user_id', 'users.user_id')
            ->leftJoin('employees as emp', 'emp.employee_id', 'users.user_id')
            ->leftJoin('user_emergency_contacts as ue', 'ue.user_id', 'users.user_id')
            ->select(
                'users.*',
                'pi.passport_no',
                'pi.passport_expiry_date',
                'pi.tel',
                'pi.nationality',
                'pi.religion',
                'pi.marital_status',
                'pi.employment_of_spouse',
                'pi.children',
                DB::raw('COALESCE(emp.birth_date, pr.birth_date) as birth_date'),
                DB::raw('COALESCE(emp.gender, pr.gender) as gender'),
                'pr.address',
                'pr.country',
                'pr.state',
                'pr.pin_code',
                'pr.phone_number',
                'pr.department',
                'pr.designation',
                'pr.reports_to',
                'ue.name_primary',
                'ue.relationship_primary',
                'ue.phone_primary',
                'ue.phone_2_primary',
                'ue.name_secondary',
                'ue.relationship_secondary',
                'ue.phone_secondary',
                'ue.phone_2_secondary'
            )
            ->where('users.user_id', $user_id)
            ->get();

        $users = DB::table('users')
            ->leftJoin('personal_information as pi', 'pi.user_id', 'users.user_id')
            ->leftJoin('profile_information as pr', 'pr.user_id', 'users.user_id')
            ->leftJoin('employees as emp', 'emp.employee_id', 'users.user_id')
            ->leftJoin('user_emergency_contacts as ue', 'ue.user_id', 'users.user_id')
            ->select(
                'users.*',
                'pi.passport_no',
                'pi.passport_expiry_date',
                'pi.tel',
                'pi.nationality',
                'pi.religion',
                'pi.marital_status',
                'pi.employment_of_spouse',
                'pi.children',
                DB::raw('COALESCE(emp.birth_date, pr.birth_date) as birth_date'),
                DB::raw('COALESCE(emp.gender, pr.gender) as gender'),
                'pr.address',
                'pr.country',
                'pr.state',
                'pr.pin_code',
                'pr.phone_number',
                'pr.department',
                'pr.designation',
                'pr.reports_to',
                'ue.name_primary',
                'ue.relationship_primary',
                'ue.phone_primary',
                'ue.phone_2_primary',
                'ue.name_secondary',
                'ue.relationship_secondary',
                'ue.phone_secondary',
                'ue.phone_2_secondary'
            )
            ->where('users.user_id', $user_id)
            ->first();

        $profileInfo = ProfileInformation::query()->where('user_id', $user_id)->first();
        $personalInfo = PersonalInformation::query()->where('user_id', $user_id)->first();
        $emergencyContact = UserEmergencyContact::query()->where('user_id', $user_id)->first();
        $bankInfo = \App\Models\BankInformation::query()->where('user_id', $user_id)->first();
        $statutoryProfile = EmployeeStatutoryProfile::query()->where('user_id', $user_id)->first();
        $familyMembers = EmployeeFamilyMember::query()->where('user_id', $user_id)->orderBy('id')->get();
        $educations = EmployeeEducation::query()->where('user_id', $user_id)->orderByDesc('end_date')->orderByDesc('start_date')->get();
        $experiences = EmployeeExperience::query()->where('user_id', $user_id)->orderByDesc('is_current')->orderByDesc('end_date')->orderByDesc('start_date')->get();
        $documents = EmployeeDocument::query()->where('user_id', $user_id)->orderByDesc('created_at')->get();
        $references = EmployeeReference::query()->where('user_id', $user_id)->orderByDesc('is_verified')->orderBy('referee_name')->get();
        $projectSnapshots = TimesheetEntry::query()
            ->where('user_id', $user_id)
            ->select(
                'project_name',
                DB::raw('COUNT(*) as entry_count'),
                DB::raw('MAX(work_date) as last_activity'),
                DB::raw('SUM(assigned_hours) as assigned_hours'),
                DB::raw('SUM(worked_hours) as worked_hours')
            )
            ->groupBy('project_name')
            ->orderByDesc('last_activity')
            ->limit(12)
            ->get()
            ->map(function ($row) {
                $assigned = max(0, (int) ($row->assigned_hours ?? 0));
                $worked = max(0, (int) ($row->worked_hours ?? 0));
                $progress = $assigned > 0 ? min(100, (int) round(($worked / $assigned) * 100)) : 0;

                return (object) [
                    'project_name' => $row->project_name,
                    'entry_count' => (int) ($row->entry_count ?? 0),
                    'last_activity' => $row->last_activity,
                    'assigned_hours' => $assigned,
                    'worked_hours' => $worked,
                    'progress' => $progress,
                ];
            });

        $profileCompletion = $this->calculateProfileCompletion(
            $profileInfo,
            $personalInfo,
            $emergencyContact,
            $bankInfo,
            $statutoryProfile,
            $educations,
            $experiences,
            $documents,
            $references
        );

        return view('employees.employeeprofile', compact(
            'user',
            'users',
            'profileInfo',
            'personalInfo',
            'emergencyContact',
            'bankInfo',
            'statutoryProfile',
            'familyMembers',
            'educations',
            'experiences',
            'documents',
            'references',
            'profileCompletion',
            'projectSnapshots'
        ));
    }

    /** page departments */
    public function index()
    {
        $departments = DB::table('departments')->get();
        return view('employees.departments', compact('departments'));
    }

    /** save record department */
    public function saveRecordDepartment(Request $request)
    {
        $request->validate([
            'department' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $department = department::where('department', $request->department)->first();
            if ($department === null) {
                $department = new department;
                $department->department = $request->department;
                $department->save();

                DB::commit();
                Toastr::success('Add new department successfully :)', 'Success');
                return redirect()->back();
            }

            DB::rollBack();
            Toastr::error('Add new department exits :)', 'Error');
            return redirect()->back();
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error('Add new department fail :)', 'Error');
            return redirect()->back();
        }
    }

    /** update record department */
    public function updateRecordDepartment(Request $request)
    {
        DB::beginTransaction();
        try {
            $department = [
                'id' => $request->id,
                'department' => $request->department,
            ];
            department::where('id', $request->id)->update($department);

            DB::commit();
            Toastr::success('updated record successfully :)', 'Success');
            return redirect()->back();
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error('updated record fail :)', 'Error');
            return redirect()->back();
        }
    }

    /** delete record department */
    public function deleteRecordDepartment(Request $request)
    {
        try {
            department::destroy($request->id);
            Toastr::success('Department deleted successfully :)', 'Success');
            return redirect()->back();
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error('Department delete fail :)', 'Error');
            return redirect()->back();
        }
    }

    /** page designations */
    public function designationsIndex()
    {
        return view('employees.designations');
    }

    /** page time sheet */
    public function timeSheetIndex()
    {
        $query = $this->timesheetBaseQuery();
        if (! $this->isAdminUser()) {
            $query->where('timesheet_entries.user_id', Auth::user()->user_id);
        }

        $timesheets = $query->orderByDesc('timesheet_entries.work_date')->get();
        $users = $this->isAdminUser()
            ? DB::table('users')->orderBy('name')->get()
            : collect([Auth::user()]);
        $summary = [
            'entries' => $timesheets->count(),
            'assigned_hours' => (int) $timesheets->sum('assigned_hours'),
            'worked_hours' => (int) $timesheets->sum('worked_hours'),
            'active_projects' => $timesheets->pluck('project_name')->filter()->unique()->count(),
        ];

        return view('employees.timesheet', compact('timesheets', 'users', 'summary'));
    }

    /** save record timesheet */
    public function saveRecordTimeSheets(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string|exists:users,user_id',
            'work_date' => 'required|date',
            'project_name' => 'required|string|max:255',
            'assigned_hours' => 'required|integer|min:0|max:24',
            'worked_hours' => [
                'required',
                'integer',
                'min:0',
                'max:24',
                function ($attribute, $value, $fail) use ($request) {
                    $assigned = (int) $request->input('assigned_hours', 0);
                    $worked = (int) $value;
                    $maxAllowed = min($assigned + 4, 24);

                    if ($worked > $maxAllowed) {
                        $fail("Worked hours cannot exceed assigned hours by more than 4 (max {$maxAllowed}).");
                    }
                },
            ],
            'description' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $payload = $request->only([
                'work_date',
                'project_name',
                'assigned_hours',
                'worked_hours',
                'description',
            ]);
            $payload['user_id'] = $this->resolveManagedUserId($request->input('user_id'));

            $entry = TimesheetEntry::create($payload);

            InAppNotifier::notifyUserId(
                (string) $entry->user_id,
                'Timesheet submitted',
                sprintf('Timesheet for %s (%s hrs) has been submitted.', (string) $entry->project_name, (string) $entry->worked_hours),
                route('employee/timesheets'),
                'info'
            );

            InAppNotifier::notifyRoles(
                ['Super Admin', 'Admin', 'HR Manager', 'Operations Manager'],
                'New timesheet entry',
                sprintf('A new timesheet entry was submitted for %s.', (string) $entry->project_name),
                url('/admin/timesheet-entries'),
                'info',
                [(string) $entry->user_id]
            );

            DB::commit();
            Toastr::success('Timesheet entry created successfully :)', 'Success');
            return redirect()->route('employee/timesheets');
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error('Timesheet entry creation failed :)', 'Error');
            return redirect()->back()->withInput();
        }
    }

    /** update record timesheet */
    public function updateRecordTimeSheets(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:timesheet_entries,id',
            'user_id' => 'required|string|exists:users,user_id',
            'work_date' => 'required|date',
            'project_name' => 'required|string|max:255',
            'assigned_hours' => 'required|integer|min:0|max:24',
            'worked_hours' => [
                'required',
                'integer',
                'min:0',
                'max:24',
                function ($attribute, $value, $fail) use ($request) {
                    $assigned = (int) $request->input('assigned_hours', 0);
                    $worked = (int) $value;
                    $maxAllowed = min($assigned + 4, 24);

                    if ($worked > $maxAllowed) {
                        $fail("Worked hours cannot exceed assigned hours by more than 4 (max {$maxAllowed}).");
                    }
                },
            ],
            'description' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $entry = TimesheetEntry::query()->findOrFail((int) $request->id);
            $this->assertCanManageEntryOwner((string) $entry->user_id);

            $payload = $request->only([
                'work_date',
                'project_name',
                'assigned_hours',
                'worked_hours',
                'description',
            ]);
            $payload['user_id'] = $this->resolveManagedUserId($request->input('user_id'), (string) $entry->user_id);

            $entry->update($payload);

            DB::commit();
            Toastr::success('Timesheet entry updated successfully :)', 'Success');
            return redirect()->route('employee/timesheets');
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error('Timesheet entry update failed :)', 'Error');
            return redirect()->back()->withInput();
        }
    }

    /** delete record timesheet */
    public function deleteRecordTimeSheets(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:timesheet_entries,id',
        ]);

        try {
            $entry = TimesheetEntry::query()->findOrFail((int) $request->id);
            $this->assertCanManageEntryOwner((string) $entry->user_id);
            $entry->delete();
            Toastr::success('Timesheet entry deleted successfully :)', 'Success');
            return redirect()->route('employee/timesheets');
        } catch (\Throwable $e) {
            Toastr::error('Timesheet entry delete failed :)', 'Error');
            return redirect()->back();
        }
    }

    /** page overtime */
    public function overTimeIndex()
    {
        $query = $this->overtimeBaseQuery();
        if (! $this->isAdminUser()) {
            $query->where('overtime_entries.user_id', Auth::user()->user_id);
        }

        $overtimeEntries = $query->orderByDesc('overtime_entries.ot_date')->get();
        $users = $this->isAdminUser()
            ? DB::table('users')->orderBy('name')->get()
            : collect([Auth::user()]);
        $summary = [
            'entries' => $overtimeEntries->count(),
            'hours' => (float) $overtimeEntries->sum('hours'),
            'pending' => $overtimeEntries->where('status', 'Pending')->count(),
            'approved' => $overtimeEntries->where('status', 'Approved')->count(),
        ];

        return view('employees.overtime', compact('overtimeEntries', 'users', 'summary'));
    }

    /** save record overtime */
    public function saveRecordOverTime(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string|exists:users,user_id',
            'ot_date' => 'required|date',
            'hours' => 'required|numeric|min:0.5|max:24',
            'ot_type' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'approved_by' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $payload = $request->only([
                'ot_date',
                'hours',
                'ot_type',
                'status',
                'approved_by',
                'description',
            ]);
            $payload['user_id'] = $this->resolveManagedUserId($request->input('user_id'));

            OvertimeEntry::create($payload);

            DB::commit();
            Toastr::success('Overtime entry created successfully :)', 'Success');
            return redirect()->route('employee/overtime');
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error('Overtime entry creation failed :)', 'Error');
            return redirect()->back()->withInput();
        }
    }

    /** update record overtime */
    public function updateRecordOverTime(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:overtime_entries,id',
            'user_id' => 'required|string|exists:users,user_id',
            'ot_date' => 'required|date',
            'hours' => 'required|numeric|min:0.5|max:24',
            'ot_type' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'approved_by' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $entry = OvertimeEntry::query()->findOrFail((int) $request->id);
            $this->assertCanManageEntryOwner((string) $entry->user_id);

            $payload = $request->only([
                'ot_date',
                'hours',
                'ot_type',
                'status',
                'approved_by',
                'description',
            ]);
            $payload['user_id'] = $this->resolveManagedUserId($request->input('user_id'), (string) $entry->user_id);

            $entry->update($payload);

            DB::commit();
            Toastr::success('Overtime entry updated successfully :)', 'Success');
            return redirect()->route('employee/overtime');
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error('Overtime entry update failed :)', 'Error');
            return redirect()->back()->withInput();
        }
    }

    /** delete record overtime */
    public function deleteRecordOverTime(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:overtime_entries,id',
        ]);

        try {
            $entry = OvertimeEntry::query()->findOrFail((int) $request->id);
            $this->assertCanManageEntryOwner((string) $entry->user_id);
            $entry->delete();
            Toastr::success('Overtime entry deleted successfully :)', 'Success');
            return redirect()->route('employee/overtime');
        } catch (\Throwable $e) {
            Toastr::error('Overtime entry delete failed :)', 'Error');
            return redirect()->back();
        }
    }


    private function timesheetBaseQuery()
    {
        return DB::table('timesheet_entries')
            ->join('users', 'users.user_id', '=', 'timesheet_entries.user_id')
            ->select('timesheet_entries.*', 'users.name', 'users.position', 'users.avatar');
    }

    private function overtimeBaseQuery()
    {
        return DB::table('overtime_entries')
            ->join('users', 'users.user_id', '=', 'overtime_entries.user_id')
            ->select('overtime_entries.*', 'users.name', 'users.position', 'users.avatar');
    }

    private function employeeBaseQuery()
    {
        return DB::table('users')
            ->join('employees', 'users.user_id', '=', 'employees.employee_id')
            ->select('users.*', 'employees.id as employee_row_id', 'employees.birth_date', 'employees.gender', 'employees.company')
            ->orderBy('users.name');
    }

    private function availableUserList()
    {
        return DB::table('users')
            ->leftJoin('employees', 'users.user_id', '=', 'employees.employee_id')
            ->whereNull('employees.employee_id')
            ->select('users.*')
            ->orderBy('users.name');
    }

    private function applyEmployeeFilters(Request $request)
    {
        return $this->employeeBaseQuery()
            ->when($request->employee_id, function ($query, $employeeId) {
                $query->where('users.user_id', 'LIKE', '%' . $employeeId . '%');
            })
            ->when($request->name, function ($query, $name) {
                $query->where('users.name', 'LIKE', '%' . $name . '%');
            })
            ->when($request->position, function ($query, $position) {
                $query->where('users.position', 'LIKE', '%' . $position . '%');
            });
    }

    private function isAdminUser(): bool
    {
        return (bool) Auth::user()?->isAdmin();
    }

    private function resolveManagedUserId(?string $requestedUserId, ?string $fallbackUserId = null): string
    {
        $authUser = Auth::user();
        abort_unless($authUser !== null, 403);

        if ($this->isAdminUser()) {
            $candidate = trim((string) ($requestedUserId ?: $fallbackUserId ?: ''));
            abort_unless($candidate !== '', 403);
            return $candidate;
        }

        return (string) $authUser->user_id;
    }

    private function assertCanManageEntryOwner(string $ownerUserId): void
    {
        if ($this->isAdminUser()) {
            return;
        }

        $authUser = Auth::user();
        abort_unless($authUser && (string) $authUser->user_id === $ownerUserId, 403);
    }

    private function calculateProfileCompletion(
        ?ProfileInformation $profileInfo,
        ?PersonalInformation $personalInfo,
        ?UserEmergencyContact $emergencyContact,
        $bankInfo,
        ?EmployeeStatutoryProfile $statutoryProfile,
        $educations,
        $experiences,
        $documents,
        $references
    ): int {
        $score = 0;

        if ($profileInfo && trim((string) ($profileInfo->phone_number ?? '')) !== '' && trim((string) ($profileInfo->address ?? '')) !== '') {
            $score += 20;
        }

        if ($personalInfo && (trim((string) ($personalInfo->nationality ?? '')) !== '' || trim((string) ($personalInfo->marital_status ?? '')) !== '')) {
            $score += 15;
        }

        if ($emergencyContact && trim((string) ($emergencyContact->name_primary ?? '')) !== '' && trim((string) ($emergencyContact->phone_primary ?? '')) !== '') {
            $score += 10;
        }

        if ($bankInfo && trim((string) (($bankInfo->primary_bank_name ?? $bankInfo->bank_name ?? ''))) !== '' && trim((string) (($bankInfo->primary_bank_account_no ?? $bankInfo->bank_account_no ?? ''))) !== '') {
            $score += 10;
        }

        if ($statutoryProfile && trim((string) ($statutoryProfile->tax_residency_state ?? '')) !== '') {
            $score += 10;
        }

        if (($educations?->count() ?? 0) > 0) {
            $score += 10;
        }

        if (($experiences?->count() ?? 0) > 0) {
            $score += 10;
        }

        if (($documents?->count() ?? 0) > 0) {
            $score += 10;
        }

        if (($references?->count() ?? 0) > 0) {
            $score += 5;
        }

        return (int) $score;
    }

    private function buildPermissionRows($employeeId, array $permissionNames, array $rowIds, array $read, array $write, array $create, array $delete, array $import, array $export)
    {
        $rows = [];
        $total = count($permissionNames);

        for ($i = 0; $i < $total; $i++) {
            $rows[] = [
                'id' => $rowIds[$i] ?? null,
                'employee_id' => $employeeId,
                'module_permission' => $permissionNames[$i] ?? null,
                'id_count' => $rowIds[$i] ?? null,
                'read' => $read[$i] ?? 'N',
                'write' => $write[$i] ?? 'N',
                'create' => $create[$i] ?? 'N',
                'delete' => $delete[$i] ?? 'N',
                'import' => $import[$i] ?? 'N',
                'export' => $export[$i] ?? 'N',
            ];
        }

        return $rows;
    }
}
