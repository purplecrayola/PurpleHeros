<?php

namespace App\Http\Controllers;

use App\Exports\SalaryExcel;
use App\Models\StaffSalary;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class PayrollController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $method = $request->route()?->getActionMethod();

            if ($method === 'salaryView') {
                abort_unless($user && $user->canAccessUserId((string) $request->route('user_id')), 403);
                return $next($request);
            }

            abort_unless($user && $user->isAdmin(), 403);

            return $next($request);
        });
    }

    /** view page salary */
    public function salary()
    {
        $users = $this->salaryBaseQuery()->orderBy('users.name')->get();
        $userList = DB::table('users')
            ->leftJoin('staff_salaries', 'users.user_id', '=', 'staff_salaries.user_id')
            ->whereNull('staff_salaries.user_id')
            ->select('users.*')
            ->orderBy('users.name')
            ->get();
        $permission_lists = DB::table('permission_lists')->get();

        return view('payroll.employeesalary', compact('users', 'userList', 'permission_lists'));
    }

    /** save record */
    public function saveRecord(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'required|string|max:255',
            'salary' => 'required|string|max:255',
            'basic' => 'required|string|max:255',
            'da' => 'required|string|max:255',
            'hra' => 'required|string|max:255',
            'conveyance' => 'required|string|max:255',
            'allowance' => 'required|string|max:255',
            'medical_allowance' => 'required|string|max:255',
            'tds' => 'required|string|max:255',
            'esi' => 'required|string|max:255',
            'pf' => 'required|string|max:255',
            'leave' => 'required|string|max:255',
            'prof_tax' => 'required|string|max:255',
            'labour_welfare' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $salary = StaffSalary::updateOrCreate(['user_id' => $request->user_id]);
            $salary->name = $request->name;
            $salary->user_id = $request->user_id;
            $salary->salary = $request->salary;
            $salary->basic = $request->basic;
            $salary->da = $request->da;
            $salary->hra = $request->hra;
            $salary->conveyance = $request->conveyance;
            $salary->allowance = $request->allowance;
            $salary->medical_allowance = $request->medical_allowance;
            $salary->tds = $request->tds;
            $salary->esi = $request->esi;
            $salary->pf = $request->pf;
            $salary->leave = $request->leave;
            $salary->prof_tax = $request->prof_tax;
            $salary->labour_welfare = $request->labour_welfare;
            $salary->save();

            DB::commit();
            Toastr::success('Create new Salary successfully :)', 'Success');
            return redirect()->back();
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error('Add Salary fail :)', 'Error');
            return redirect()->back();
        }
    }

    /** salary view detail */
    public function salaryView($user_id)
    {
        $users = $this->salaryDetailQuery($user_id)->first();

        if (! empty($users)) {
            return view('payroll.salaryview', compact('users'));
        }

        Toastr::warning('Please update information user :)', 'Warning');
        return redirect()->route('form/salary/page');
    }

    /** update record */
    public function updateRecord(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'name' => 'required|string|max:255',
            'salary' => 'required|string|max:255',
            'basic' => 'required|string|max:255',
            'da' => 'required|string|max:255',
            'hra' => 'required|string|max:255',
            'conveyance' => 'required|string|max:255',
            'allowance' => 'required|string|max:255',
            'medical_allowance' => 'required|string|max:255',
            'tds' => 'required|string|max:255',
            'esi' => 'required|string|max:255',
            'pf' => 'required|string|max:255',
            'leave' => 'required|string|max:255',
            'prof_tax' => 'required|string|max:255',
            'labour_welfare' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            StaffSalary::where('id', $request->id)->update([
                'name' => $request->name,
                'salary' => $request->salary,
                'basic' => $request->basic,
                'da' => $request->da,
                'hra' => $request->hra,
                'conveyance' => $request->conveyance,
                'allowance' => $request->allowance,
                'medical_allowance' => $request->medical_allowance,
                'tds' => $request->tds,
                'esi' => $request->esi,
                'pf' => $request->pf,
                'leave' => $request->leave,
                'prof_tax' => $request->prof_tax,
                'labour_welfare' => $request->labour_welfare,
            ]);

            DB::commit();
            Toastr::success('Salary updated successfully :)', 'Success');
            return redirect()->back();
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error('Salary update fail :)', 'Error');
            return redirect()->back();
        }
    }

    /** delete record */
    public function deleteRecord(Request $request)
    {
        DB::beginTransaction();
        try {
            StaffSalary::destroy($request->id);
            DB::commit();
            Toastr::success('Salary deleted successfully :)', 'Success');
            return redirect()->back();
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error('Salary deleted fail :)', 'Error');
            return redirect()->back();
        }
    }

    /** payroll Items */
    public function payrollItems()
    {
        return view('payroll.payrollitems');
    }

    /** report pdf */
    public function reportPDF(Request $request)
    {
        $user_id = $request->user_id;
        $users = $this->salaryDetailQuery($user_id)->first();

        $pdf = PDF::loadView('report_template.salary_pdf', compact('users'))->setPaper('a4', 'landscape');
        return $pdf->download('ReportDetailSalary.pdf');
    }

    /** export Excel */
    public function reportExcel(Request $request)
    {
        return Excel::download(new SalaryExcel($request->user_id), 'ReportDetailSalary.xlsx');
    }

    private function salaryBaseQuery()
    {
        return DB::table('users')
            ->join('staff_salaries', 'users.user_id', '=', 'staff_salaries.user_id')
            ->select('users.*', 'staff_salaries.*', 'staff_salaries.id as salary_row_id');
    }

    private function salaryDetailQuery($userId)
    {
        return DB::table('users')
            ->join('staff_salaries', 'users.user_id', '=', 'staff_salaries.user_id')
            ->leftJoin('profile_information', 'users.user_id', '=', 'profile_information.user_id')
            ->select('users.*', 'staff_salaries.*', 'profile_information.*')
            ->where('staff_salaries.user_id', $userId);
    }
}
