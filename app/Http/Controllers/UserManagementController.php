<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use DB;
use App\Models\User;
use App\Models\Employee;
use App\Models\Form;
use App\Models\ProfileInformation;
use App\Models\PersonalInformation;
use App\Models\EmployeeStatutoryProfile;
use App\Models\EmployeeFamilyMember;
use App\Models\EmployeeEducation;
use App\Models\EmployeeExperience;
use App\Models\EmployeeDocument;
use App\Models\EmployeeReference;
use App\Models\StaffSalary;
use App\Models\TimesheetEntry;
use App\Support\MediaStorageManager;
use App\Rules\MatchOldPassword;
use App\Models\UserEmergencyContact;
use App\Models\BankInformation;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Session;
use Auth;
use Hash;

class UserManagementController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /** index page */
    public function index()
    {
        if (! $this->canManageUsers()) {
            return redirect()->route('home');
        }

        return redirect('/admin/users');
    }

    /** get list data and search */
    public function getUsersData(Request $request)
    {
        $draw            = $request->get('draw');
        $start           = $request->get("start");
        $rowPerPage      = $request->get("length"); // total number of rows per page
        $columnIndex_arr = $request->get('order');
        $columnName_arr  = $request->get('columns');
        $order_arr       = $request->get('order');
        $search_arr      = $request->get('search');

        $columnIndex     = $columnIndex_arr[0]['column']; // Column index
        $columnName      = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue     = $search_arr['value']; // Search value

        $users =  DB::table('users');
        $totalRecords = $users->count();

        $user_name   = $request->user_name;
        $type_role   = $request->type_role;
        $type_status = $request->type_status;

        /** search for name */
        if(!empty($user_name)) {
            $users->when($user_name,function($query) use ($user_name){
                $query->where('name','LIKE','%'.$user_name.'%');
            });
        }

        /** search for type_role */
        if(!empty($type_role)) {
            $users->when($type_role,function($query) use ($type_role){
                $query->where('role_name',$type_role);
            });
        }

        /** search for status */
        if(!empty($type_status)) {
            $users->when($type_status,function($query) use ($type_status){
                $query->where('status',$type_status);
            });
        }

        $totalRecordsWithFilter = $users->where(function ($query) use ($searchValue) {
            $query->where('name', 'like', '%' . $searchValue . '%');
            $query->orWhere('user_id', 'like', '%' . $searchValue . '%');
            $query->orWhere('email', 'like', '%' . $searchValue . '%');
            $query->orWhere('position', 'like', '%' . $searchValue . '%');
            $query->orWhere('phone_number', 'like', '%' . $searchValue . '%');
            $query->orWhere('join_date', 'like', '%' . $searchValue . '%');
            $query->orWhere('role_name', 'like', '%' . $searchValue . '%');
            $query->orWhere('status', 'like', '%' . $searchValue . '%');
            $query->orWhere('department', 'like', '%' . $searchValue . '%');
        })->count();

        if ($columnName == 'user_id') {
            $columnName = 'user_id';
        }
        $records = $users->orderBy($columnName, $columnSortOrder)
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%');
                $query->orWhere('user_id', 'like', '%' . $searchValue . '%');
                $query->orWhere('email', 'like', '%' . $searchValue . '%');
                $query->orWhere('position', 'like', '%' . $searchValue . '%');
                $query->orWhere('phone_number', 'like', '%' . $searchValue . '%');
                $query->orWhere('join_date', 'like', '%' . $searchValue . '%');
                $query->orWhere('role_name', 'like', '%' . $searchValue . '%');
                $query->orWhere('status', 'like', '%' . $searchValue . '%');
                $query->orWhere('department', 'like', '%' . $searchValue . '%');
            })
            ->skip($start)
            ->take($rowPerPage)
            ->get();
        $data_arr = [];
        foreach ($records as $key => $record) {
            $avatarUrl = MediaStorageManager::publicUrl($record->avatar, 'assets/img/user.jpg', 'assets/images');
            $record->name = '<h2 class="table-avatar"><a href="'.url('employee/profile/' . $record->user_id).'" class="name">'.'<img class="avatar" data-avatar='.$record->avatar.' src="'.$avatarUrl.'">' .$record->name.'</a></h2>';
            if ($record->role_name == 'Admin') { /** color role name */
                $role_name = '<span class="badge bg-inverse-danger role_name">'.$record->role_name.'</span>';
            } elseif ($record->role_name == 'Super Admin') {
                $role_name = '<span class="badge bg-inverse-warning role_name">'.$record->role_name.'</span>';
            } elseif ($record->role_name == 'Normal User') {
                $role_name = '<span class="badge bg-inverse-info role_name">'.$record->role_name.'</span>';
            } elseif ($record->role_name == 'Client') {
                $role_name = '<span class="badge bg-inverse-success role_name">'.$record->role_name.'</span>'; 
            } elseif ($record->role_name == 'Employee') {
                $role_name = '<span class="badge bg-inverse-dark role_name">'.$record->role_name.'</span>'; 
            } else {
                $role_name = 'NULL'; /** null role name */
            }

            /** status */
            $full_status = '
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item"><i class="fa fa-dot-circle-o text-success"></i> Active </a>
                    <a class="dropdown-item"><i class="fa fa-dot-circle-o text-warning"></i> Inactive </a>
                    <a class="dropdown-item"><i class="fa fa-dot-circle-o text-danger"></i> Disable </a>
                </div>
            ';

            if ($record->status == 'Active') {
                $status = '
                    <a class="btn btn-white btn-sm btn-rounded dropdown-toggle" href="#" data-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-dot-circle-o text-success"></i>
                        <span class="status_s">'.$record->status.'</span>
                    </a>
                    '.$full_status.'
                ';
            } elseif ($record->status == 'Inactive') {
                $status = '
                    <a class="btn btn-white btn-sm btn-rounded dropdown-toggle" href="#" data-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-dot-circle-o text-info"></i>
                        <span class="status_s">'.$record->status.'</span>
                    </a>
                    '.$full_status.'
                ';
            } elseif ($record->status == 'Disable') {
                $status = '
                    <a class="btn btn-white btn-sm btn-rounded dropdown-toggle" href="#" data-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-dot-circle-o text-danger"></i>
                        <span class="status_s">'.$record->status.'</span>
                    </a>
                    '.$full_status.'
                ';
            } else {
                $status = '
                    <a class="btn btn-white btn-sm btn-rounded dropdown-toggle" href="#" data-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-dot-circle-o text-dark"></i>
                        <span class="statuss">'.$record->status.'</span>
                    </a>
                    '.$full_status.'
                ';
            }

            $last_login = Carbon::parse($record->last_login)->diffForHumans();

            $data_arr [] = [
                "no"           => '<span class="id" data-id = '.$record->id.'>'.$start + ($key + 1).'</span>',
                "name"         => $record->name,
                "user_id"      => '<span class="user_id">'.$record->user_id.'</span>',
                "email"        => '<span class="email">'.$record->email.'</span>',
                "position"     => '<span class="position">'.$record->position.'</span>',
                "phone_number" => '<span class="phone_number">'.$record->phone_number.'</span>',
                "join_date"    => $record->join_date,
                "last_login"   => $last_login,
                "role_name"    => $role_name,
                "status"       => $status,
                "department"   => '<span class="department">'.$record->department.'</span>',
                "action"       => 
                '
                <td>
                    <div class="dropdown dropdown-action">
                        <a class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <i class="material-icons">more_vert</i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item userUpdate" data-toggle="modal" data-id="'.$record->id.'" data-target="#edit_user">
                                <i class="fa fa-pencil m-r-5"></i> Edit
                            </a>
                            <a class="dropdown-item userDelete" data-toggle="modal" data-id="'.$record->id.'" data-target="#delete_user">
                                <i class="fa fa-trash-o m-r-5"></i> Delete
                            </a>
                        </div>
                    </div>
                </td>
                ',
            ];
        }
        $response = [
            "draw"                 => intval($draw),
            "iTotalRecords"        => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordsWithFilter,
            "aaData"               => $data_arr
        ];
        return response()->json($response);
    }

    /** profile user */
    public function profile()
    {   
        $this->authorizeUserAccess(Auth::user()?->user_id);
        $profile = Session::get('user_id'); // get user_id session
        $userInformation = PersonalInformation::where('user_id',$profile)->first(); // user information
        $bankInformation = BankInformation::where('user_id',$profile)->first(); // user information
        $statutoryProfile = EmployeeStatutoryProfile::where('user_id', $profile)->first();
        $salaryRecord = StaffSalary::where('user_id', $profile)->first();
        $familyMembers = EmployeeFamilyMember::where('user_id', $profile)->orderBy('id')->get();
        $educations = EmployeeEducation::where('user_id', $profile)->orderByDesc('end_date')->orderByDesc('start_date')->get();
        $experiences = EmployeeExperience::where('user_id', $profile)->orderByDesc('is_current')->orderByDesc('end_date')->orderByDesc('start_date')->get();
        $references = EmployeeReference::where('user_id', $profile)->orderByDesc('is_verified')->orderBy('referee_name')->get();
        $documents = EmployeeDocument::where('user_id', $profile)->orderByDesc('created_at')->get();
        $projectSnapshots = TimesheetEntry::query()
            ->where('user_id', $profile)
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
            $information = ProfileInformation::where('user_id', $profile)->first(),
            $userInformation,
            $emergencyContact = UserEmergencyContact::where('user_id', Session::get('user_id'))->first(),
            $bankInformation,
            $statutoryProfile,
            $educations,
            $experiences,
            $documents,
            $references
        );
        $employeeCore = Employee::query()
            ->where('employee_id', $profile)
            ->first(['employee_id', 'birth_date', 'gender']);

        $canonicalBirthDate = $this->normalizeDateInput($employeeCore?->birth_date)
            ?? $this->normalizeDateInput($information?->birth_date);

        if (! $information && $employeeCore) {
            $information = new ProfileInformation();
            $information->user_id = $profile;
            $information->birth_date = $canonicalBirthDate;
            $information->gender = $employeeCore->gender;
        } elseif ($information) {
            if ($canonicalBirthDate !== null) {
                $information->birth_date = $canonicalBirthDate;
            }
            if ((trim((string) $information->gender) === '') && $employeeCore && trim((string) $employeeCore->gender) !== '') {
                $information->gender = $employeeCore->gender;
            }
        }

        $user = DB::table('users')->get();
        $employees = DB::table('profile_information')->where('user_id',$profile)->first();

        if (empty($employees))
        {
            return view('usermanagement.profile_user',compact('information','user','userInformation','emergencyContact','bankInformation', 'statutoryProfile', 'salaryRecord', 'familyMembers', 'educations', 'experiences', 'references', 'documents', 'profileCompletion', 'projectSnapshots'));

        } else {
            $user_id = $employees->user_id;
            if ($user_id == $profile)
            {
                return view('usermanagement.profile_user',compact('information','user','userInformation','emergencyContact','bankInformation', 'statutoryProfile', 'salaryRecord', 'familyMembers', 'educations', 'experiences', 'references', 'documents', 'profileCompletion', 'projectSnapshots'));
            } else {
                $information = ProfileInformation::all();
                return view('usermanagement.profile_user',compact('information','user','userInformation','emergencyContact','bankInformation', 'statutoryProfile', 'salaryRecord', 'familyMembers', 'educations', 'experiences', 'references', 'documents', 'profileCompletion', 'projectSnapshots'));
            } 
        }
    }

    public function saveOnboardingData(Request $request)
    {
        $this->authorizeUserAccess($request->input('user_id'));

        $request->validate([
            'user_id' => 'required|string|exists:users,user_id',
            'educations' => 'nullable|array',
            'educations.*.institution' => 'nullable|string|max:255',
            'educations.*.degree' => 'nullable|string|max:255',
            'educations.*.field_of_study' => 'nullable|string|max:255',
            'educations.*.start_date' => 'nullable|date',
            'educations.*.end_date' => 'nullable|date',
            'educations.*.grade' => 'nullable|string|max:255',
            'experiences' => 'nullable|array',
            'experiences.*.company_name' => 'nullable|string|max:255',
            'experiences.*.job_title' => 'nullable|string|max:255',
            'experiences.*.location' => 'nullable|string|max:255',
            'experiences.*.start_date' => 'nullable|date',
            'experiences.*.end_date' => 'nullable|date',
            'experiences.*.is_current' => 'nullable|boolean',
            'experiences.*.summary' => 'nullable|string|max:2000',
            'references' => 'nullable|array',
            'references.*.referee_name' => 'nullable|string|max:255',
            'references.*.relationship' => 'nullable|string|max:255',
            'references.*.company_name' => 'nullable|string|max:255',
            'references.*.job_title' => 'nullable|string|max:255',
            'references.*.email' => 'nullable|email|max:255',
            'references.*.phone' => 'nullable|string|max:255',
            'references.*.years_known' => 'nullable|string|max:255',
            'references.*.is_verified' => 'nullable|boolean',
            'references.*.verification_feedback' => 'nullable|string|max:2000',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'certification_files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        DB::beginTransaction();

        try {
            $userId = (string) $request->input('user_id');
            $actorId = Auth::user()?->user_id;

            if ($request->has('educations')) {
                EmployeeEducation::query()->where('user_id', $userId)->delete();
                foreach ($request->input('educations', []) as $education) {
                    if (trim((string) ($education['institution'] ?? '')) === '') {
                        continue;
                    }

                    EmployeeEducation::query()->create([
                        'user_id' => $userId,
                        'institution' => $education['institution'] ?? null,
                        'degree' => $education['degree'] ?? null,
                        'field_of_study' => $education['field_of_study'] ?? null,
                        'start_date' => $education['start_date'] ?? null,
                        'end_date' => $education['end_date'] ?? null,
                        'grade' => $education['grade'] ?? null,
                        'created_by_user_id' => $actorId,
                    ]);
                }
            }

            if ($request->has('experiences')) {
                EmployeeExperience::query()->where('user_id', $userId)->delete();
                foreach ($request->input('experiences', []) as $experience) {
                    if (trim((string) ($experience['company_name'] ?? '')) === '') {
                        continue;
                    }

                    EmployeeExperience::query()->create([
                        'user_id' => $userId,
                        'company_name' => $experience['company_name'] ?? null,
                        'job_title' => $experience['job_title'] ?? null,
                        'location' => $experience['location'] ?? null,
                        'start_date' => $experience['start_date'] ?? null,
                        'end_date' => $experience['end_date'] ?? null,
                        'is_current' => (bool) ($experience['is_current'] ?? false),
                        'summary' => $experience['summary'] ?? null,
                        'created_by_user_id' => $actorId,
                    ]);
                }
            }

            if ($request->has('references')) {
                EmployeeReference::query()->where('user_id', $userId)->delete();
                foreach ($request->input('references', []) as $reference) {
                    if (trim((string) ($reference['referee_name'] ?? '')) === '') {
                        continue;
                    }

                    $isAdmin = (bool) Auth::user()?->isAdmin();
                    $isVerified = $isAdmin ? (bool) ($reference['is_verified'] ?? false) : false;

                    EmployeeReference::query()->create([
                        'user_id' => $userId,
                        'referee_name' => $reference['referee_name'] ?? null,
                        'relationship' => $reference['relationship'] ?? null,
                        'company_name' => $reference['company_name'] ?? null,
                        'job_title' => $reference['job_title'] ?? null,
                        'email' => $reference['email'] ?? null,
                        'phone' => $reference['phone'] ?? null,
                        'years_known' => $reference['years_known'] ?? null,
                        'is_verified' => $isVerified,
                        'verification_feedback' => $isAdmin ? ($reference['verification_feedback'] ?? null) : null,
                        'verified_by_user_id' => $isVerified ? $actorId : null,
                        'verified_at' => $isVerified ? now() : null,
                        'created_by_user_id' => $actorId,
                    ]);
                }
            }

            if ($request->hasFile('cv_file')) {
                $existingCv = EmployeeDocument::query()
                    ->where('user_id', $userId)
                    ->where('document_type', 'cv')
                    ->first();

                if ($existingCv && $existingCv->file_path) {
                    MediaStorageManager::deletePath($existingCv->file_path);
                }

                $file = $request->file('cv_file');
                $storedFile = MediaStorageManager::storeUploadedFile($file, 'assets/onboarding-documents', $userId . '-cv');

                EmployeeDocument::query()->updateOrCreate(
                    ['user_id' => $userId, 'document_type' => 'cv'],
                    [
                        'title' => 'Curriculum Vitae',
                        'file_path' => $storedFile['path'],
                        'uploaded_by_user_id' => $actorId,
                        'is_verified' => false,
                        'verification_feedback' => null,
                        'verified_by_user_id' => null,
                        'verified_at' => null,
                    ]
                );
            }

            foreach (($request->file('certification_files') ?? []) as $file) {
                if (! $file) {
                    continue;
                }

                $storedFile = MediaStorageManager::storeUploadedFile($file, 'assets/onboarding-documents', $userId . '-cert');

                EmployeeDocument::query()->create([
                    'user_id' => $userId,
                    'document_type' => 'certification',
                    'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'file_path' => $storedFile['path'],
                    'uploaded_by_user_id' => $actorId,
                ]);
            }

            DB::commit();
            Toastr::success('Onboarding information updated successfully :)', 'Success');

            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);
            Toastr::error('Could not update onboarding information :)', 'Error');

            return redirect()->back()->withInput();
        }
    }

    /** save bank and statutory profile data from legacy profile tab */
    public function saveBankStatutory(Request $request)
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
        $this->authorizeUserAccess($request->input('user_id'));

        $request->validate([
            'user_id' => 'required|string|exists:users,user_id',
            'salary_amount' => 'nullable|numeric|min:0',
            'tax_station' => 'nullable|string|max:255',
            'tax_residency_state' => 'nullable|string|max:255',
            'annual_rent' => 'nullable|numeric|min:0',
            'other_statutory_deductions' => 'nullable|numeric|min:0',
            'default_non_taxable_reimbursement' => 'nullable|numeric|min:0',
            'employee_pension_rate_percent' => 'nullable|numeric|min:0|max:100',
            'employer_pension_rate_percent' => 'nullable|numeric|min:0|max:100',
            'nhf_rate_percent' => 'nullable|numeric|min:0|max:100',
            'nhf_base_cap' => 'nullable|numeric|min:0',
            'pension_pin' => 'nullable|string|max:255',
            'nhf_number' => 'nullable|string|max:255',
        ]);

        try {
            $userId = (string) $request->input('user_id');
            $user = User::query()->where('user_id', $userId)->first();

            $profile = EmployeeStatutoryProfile::query()->firstOrCreate(['user_id' => $userId]);
            $profile->fill([
                'tax_station' => $request->input('tax_station'),
                'tax_residency_state' => $request->input('tax_residency_state'),
                'pension_enabled' => (bool) $request->boolean('pension_enabled'),
                'employee_pension_rate_percent' => $request->input('employee_pension_rate_percent'),
                'employer_pension_rate_percent' => $request->input('employer_pension_rate_percent'),
                'pension_pin' => $request->input('pension_pin'),
                'nhf_enabled' => (bool) $request->boolean('nhf_enabled'),
                'nhf_rate_percent' => $request->input('nhf_rate_percent'),
                'nhf_base_cap' => $request->input('nhf_base_cap'),
                'nhf_number' => $request->input('nhf_number'),
                'annual_rent' => (float) ($request->input('annual_rent', 0) ?: 0),
                'other_statutory_deductions' => (float) ($request->input('other_statutory_deductions', 0) ?: 0),
                'default_non_taxable_reimbursement' => (float) ($request->input('default_non_taxable_reimbursement', 0) ?: 0),
                // India-template statutory fields are deprecated in Nigeria flow.
                'pf_enabled' => false,
                'pf_number' => null,
                'pf_contribution_rate_percent' => null,
                'pf_additional_rate_percent' => null,
                'esi_enabled' => false,
                'esi_number' => null,
                'esi_contribution_rate_percent' => null,
                'esi_additional_rate_percent' => null,
            ]);
            if (! $profile->created_by_user_id) {
                $profile->created_by_user_id = Auth::user()?->user_id;
            }
            $profile->save();

            if ($request->filled('salary_amount')) {
                StaffSalary::query()->updateOrCreate(
                    ['user_id' => $userId],
                    [
                        'name' => $user?->name ?? $userId,
                        'salary' => (string) $request->input('salary_amount'),
                    ]
                );
            }

            Toastr::success('Bank & statutory settings updated successfully', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error($e);
            Toastr::error('Could not update bank & statutory settings', 'Error');
            return redirect()->back();
        }
    }

    /** save profile information */
    public function profileInformation(Request $request)
    {
        $this->authorizeUserAccess($request->input('user_id'));
        try {
            if(!empty($request->images))
            {
                [$firstName, $lastName] = $this->splitName((string) $request->name);
                $image_name = $request->hidden_image;
                $image      = $request->file('images');
                if($image_name =='photo_defaults.jpg')
                {
                    if($image != '')
                    {
                        $storedFile = MediaStorageManager::storeUploadedFile($image, 'assets/images', $request->user_id . '-avatar');
                        $image_name = $storedFile['path'];
                    }
                } else {
                    if($image != '')
                    {
                        MediaStorageManager::deletePath(Auth::user()->avatar);
                        $storedFile = MediaStorageManager::storeUploadedFile($image, 'assets/images', $request->user_id . '-avatar');
                        $image_name = $storedFile['path'];
                    }
                }
                $update = [
                    'user_id' => $request->user_id,
                    'name'   => $request->name,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'avatar' => $image_name,
                ];
                User::where('user_id',$request->user_id)->update($update);
            } 

            [$firstName, $lastName] = $this->splitName((string) $request->name);
            $normalizedBirthDate = $this->normalizeDateInput($request->birthDate);
            $information = ProfileInformation::updateOrCreate(['user_id' => $request->user_id]);
            $information->name         = $request->name;
            $information->first_name   = $firstName;
            $information->last_name    = $lastName;
            $information->user_id      = $request->user_id;
            $information->email        = $request->email;
            $information->birth_date   = $normalizedBirthDate;
            $information->gender       = $request->gender;
            $information->address      = $request->address;
            $information->state        = $request->state;
            $information->country      = $request->country;
            $information->pin_code     = $request->pin_code;
            $information->phone_number = $request->phone_number;
            $information->department   = $request->department;
            $information->designation  = $request->designation;
            $information->reports_to   = $request->reports_to;
            $information->save();

            Employee::query()
                ->where('employee_id', $request->user_id)
                ->update([
                    'birth_date' => $normalizedBirthDate,
                    'gender' => $request->gender,
                ]);
            
            DB::commit();
            Toastr::success('Profile Information successfully :)','Success');
            return redirect()->back();
        }catch(\Exception $e){
            DB::rollback();
            Toastr::error('Add Profile Information fail :)','Error');
            return redirect()->back();
        }
    }
   
    /** save new user */
    public function addNewUserSave(Request $request)
    {
        abort_unless($this->canManageUsers(), 403);

        abort_unless($this->canManageUsers(), 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|min:11|numeric',
            'role_name' => 'required|string|max:255|exists:role_type_users,role_type',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'image' => 'nullable|image',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);
        DB::beginTransaction();
        try{
            $dt       = Carbon::now();
            $todayDate = $dt->toDayDateTimeString();

            $image = 'photo_defaults.jpg';
            if ($request->hasFile('image')) {
                $storedFile = MediaStorageManager::storeUploadedFile($request->file('image'), 'assets/images', 'user-avatar');
                $image = $storedFile['path'];
            }

            [$firstName, $lastName] = $this->splitName((string) $request->name);
            $user = new User;
            $user->name         = $request->name;
            $user->first_name   = $firstName;
            $user->last_name    = $lastName;
            $user->email        = $request->email;
            $user->join_date    = $todayDate;
            $user->last_login   = $todayDate;
            $user->phone_number = $request->phone;
            $user->role_name    = $request->role_name;
            $user->position     = $request->position;
            $user->department   = $request->department;
            $user->status       = $request->status;
            $user->avatar       = $image;
            $user->password     = Hash::make($request->password);
            $user->save();
            DB::commit();
            Toastr::success('Create new account successfully :)','Success');
            return redirect()->route('userManagement');
        }catch(\Exception $e) {
            DB::rollback();
            \Log::info($e);
            Toastr::error('User add new account fail :)','Error');
            return redirect()->back();
        }
    }
    
    /** update record */
    public function update(Request $request)
    {
        abort_unless($this->canManageUsers(), 403);

        $request->validate([
            'user_id' => 'required|string|exists:users,user_id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $request->user_id . ',user_id,user_id',
            'role_name' => 'required|string|max:255|exists:role_type_users,role_type',
            'position' => 'required|string|max:255',
            'phone' => 'required|min:11|numeric',
            'department' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'images' => 'nullable|image',
        ]);

        DB::beginTransaction();
        try{
            $user_id       = $request->user_id;
            $name         = $request->name;
            $email        = $request->email;
            $role_name    = $request->role_name;
            $position     = $request->position;
            $phone        = $request->phone;
            $department   = $request->department;
            $status       = $request->status;
            [$firstName, $lastName] = $this->splitName((string) $name);

            $dt       = Carbon::now();
            $todayDate = $dt->toDayDateTimeString();
            $image_name = $request->hidden_image;
            $image = $request->file('images');
            if($image_name =='photo_defaults.jpg') {
                if (empty($image)) {
                    $image_name = $image_name;
                } else {
                    $storedFile = MediaStorageManager::storeUploadedFile($image, 'assets/images', $request->user_id . '-avatar');
                    $image_name = $storedFile['path'];
                }
            } else {
                if (!empty($image)) {
                    MediaStorageManager::deletePath($image_name);
                    $storedFile = MediaStorageManager::storeUploadedFile($image, 'assets/images', $request->user_id . '-avatar');
                    $image_name = $storedFile['path'];
                }
            }
            
            $update = [

                'user_id'       => $user_id,
                'name'         => $name,
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'role_name'    => $role_name,
                'email'        => $email,
                'position'     => $position,
                'phone_number' => $phone,
                'department'   => $department,
                'status'       => $status,
                'avatar'       => $image_name,
            ];

            $activityLog = [
                'user_name'    => $name,
                'email'        => $email,
                'phone_number' => $phone,
                'status'       => $status,
                'role_name'    => $role_name,
                'modify_user'  => 'Update',
                'date_time'    => $todayDate,
            ];

            DB::table('user_activity_logs')->insert($activityLog);
            User::where('user_id',$request->user_id)->update($update);
            ProfileInformation::query()->where('user_id', $request->user_id)->update([
                'name' => $name,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);
            DB::commit();
            Toastr::success('User updated successfully :)','Success');
            return redirect()->route('userManagement');

        } catch(\Exception $e){
            DB::rollback();
            Toastr::error('User update fail :)','Error');
            return redirect()->back();
        }
    }

    private function splitName(string $fullName): array
    {
        $name = trim($fullName);
        if ($name === '') {
            return [null, null];
        }

        $parts = preg_split('/\s+/', $name) ?: [];
        $firstName = $parts[0] ?? null;
        $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : null;

        return [$firstName, $lastName];
    }

    private function normalizeDateInput($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $date = trim((string) $value);
        if ($date === '') {
            return null;
        }

        foreach (['Y-m-d', 'd-m-Y', 'm/d/Y', 'd/m/Y', 'M j, Y', 'F j, Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $date)->toDateString();
            } catch (\Throwable $e) {
                // try next format
            }
        }

        try {
            return Carbon::parse($date)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** delete record */
    public function delete(Request $request)
    {
        abort_unless($this->canManageUsers(), 403);

        DB::beginTransaction();
        try {

            $dt        = Carbon::now();
            $todayDate = $dt->toDayDateTimeString();

            $activityLog = [
                'user_name'    => Session::get('name'),
                'email'        => Session::get('email'),
                'phone_number' => Session::get('phone_number'),
                'status'       => Session::get('status'),
                'role_name'    => Session::get('role_name'),
                'modify_user'  => 'Delete',
                'date_time'    => $todayDate,
            ];

            DB::table('user_activity_logs')->insert($activityLog);

            $user = User::findOrFail($request->id);

            if (Auth::user()->id === $user->id) {
                DB::rollBack();
                Toastr::error('You cannot delete your own account from this screen :)','Error');
                return redirect()->back();
            }

            $avatar = $request->avatar;
            $user->delete();
            if (!empty($avatar) && $avatar !== 'photo_defaults.jpg') {
                MediaStorageManager::deletePath($avatar);
            }
            PersonalInformation::where('user_id', $user->user_id)->delete();
            UserEmergencyContact::where('user_id', $user->user_id)->delete();

            DB::commit();
            Toastr::success('User deleted successfully :)','Success');
           return redirect()->back();
        } catch(\Exception $e) {
            DB::rollback();
            Toastr::error('User deleted fail :)','Error');
            return redirect()->back();
        }
    }

    /** view change password */
    public function changePasswordView()
    {
        $this->authorizeUserAccess(Auth::user()?->user_id);
        return view('account.change-password');
    }
    
    /** change password in db */
    public function changePasswordDB(Request $request)
    {
        $this->authorizeUserAccess(Auth::user()?->user_id);
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::find(auth()->user()->id)->update(['password'=> Hash::make($request->new_password)]);
        DB::commit();
        Toastr::success('User change successfully :)','Success');
        return redirect()->intended('home');
    }

    /** user profile Emergency Contact */
    public function emergencyContactSaveOrUpdate(Request $request)
    {
        $this->authorizeUserAccess($request->input('user_id'));
        /** validate form */
        $request->validate([
            'name_primary' =>'required',
            'relationship_primary'   => 'required',
            'phone_primary'          => 'required',
            'phone_2_primary'        => 'required',
            'name_secondary'         => 'required',
            'relationship_secondary' => 'required',
            'phone_secondary'        => 'required',
            'phone_2_secondary'      => 'required',
        ]);

        try {
            
            /** save or update to databases user_emergency_contacts table */
            $saveRecord = UserEmergencyContact::updateOrCreate(['user_id' => $request->user_id]);
            $saveRecord->name_primary           = $request->name_primary;
            $saveRecord->relationship_primary   = $request->relationship_primary;
            $saveRecord->phone_primary          = $request->phone_primary;
            $saveRecord->phone_2_primary        = $request->phone_2_primary;
            $saveRecord->name_secondary         = $request->name_secondary;
            $saveRecord->relationship_secondary = $request->relationship_secondary;
            $saveRecord->phone_secondary        = $request->phone_secondary;
            $saveRecord->phone_2_secondary      = $request->phone_2_secondary;
            $saveRecord->save();
            
            DB::commit();
            Toastr::success('Add Emergency Contact successfully :)','Success');
            return redirect()->back();
        }catch(\Exception $e){
            DB::rollback();
            Toastr::error('Add Emergency Contact fail :)','Error');
            return redirect()->back();
        }
    }



    private function authorizeUserAccess(?string $userId = null): void
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        if ($user->isAdmin()) {
            return;
        }

        abort_unless($user->canAccessUserId($userId), 403);
    }

    private function canManageUsers(): bool
    {
        return Auth::check() && in_array(Auth::user()->role_name, ['Admin', 'Super Admin'], true);
    }

    private function calculateProfileCompletion(
        ?ProfileInformation $profileInfo,
        ?PersonalInformation $personalInfo,
        ?UserEmergencyContact $emergencyContact,
        ?BankInformation $bankInfo,
        ?EmployeeStatutoryProfile $statutoryProfile,
        $educations,
        $experiences,
        $documents,
        $references
    ): int {
        $score = 0;
        $weight = 100;

        if ($profileInfo && trim((string) ($profileInfo->phone_number ?? '')) !== '' && trim((string) ($profileInfo->address ?? '')) !== '') {
            $score += 20;
        }

        if ($personalInfo && (trim((string) ($personalInfo->nationality ?? '')) !== '' || trim((string) ($personalInfo->marital_status ?? '')) !== '')) {
            $score += 15;
        }

        if ($emergencyContact && trim((string) ($emergencyContact->name_primary ?? '')) !== '' && trim((string) ($emergencyContact->phone_primary ?? '')) !== '') {
            $score += 10;
        }

        if ($bankInfo && trim((string) ($bankInfo->primary_bank_name ?: $bankInfo->bank_name ?? '')) !== '' && trim((string) ($bankInfo->primary_bank_account_no ?: $bankInfo->bank_account_no ?? '')) !== '') {
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

        return (int) round(($score / $weight) * 100);
    }
}
