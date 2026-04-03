<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use App\Models\User;
use App\Models\BankInformation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PayslipPortalController extends Controller
{
    public function index()
    {
        $userId = (string) Auth::user()->user_id;

        $payslips = Payslip::query()
            ->where('user_id', $userId)
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->paginate(24);

        return view('payroll.my-payslips', compact('payslips'));
    }

    public function download(Payslip $payslip)
    {
        $authUser = Auth::user();
        abort_unless($authUser?->canAccessUserId((string) $payslip->user_id), 403);

        if ($payslip->file_path) {
            return redirect()->away($payslip->file_url);
        }

        $payload = is_array($payslip->payload) ? $payslip->payload : [];
        abort_if(empty($payload), 404, 'Payslip payload is not available for this record.');

        $employee = data_get($payload, 'employee', []);
        $input = data_get($payload, 'input', []);
        $computed = data_get($payload, 'computed', []);

        $employeeUser = User::query()
            ->where('user_id', (string) $payslip->user_id)
            ->first();
        $bankProfile = BankInformation::query()
            ->where('user_id', (string) $payslip->user_id)
            ->first();

        $employeeName = (string) (data_get($employee, 'employee_name') ?: $employeeUser?->name ?: $payslip->user_id);
        $accountNumberRaw = (string) (
            data_get($employee, 'account_number')
            ?: $bankProfile?->primary_bank_account_no
            ?: $bankProfile?->bank_account_no
            ?: $bankProfile?->secondary_bank_account_no
            ?: ''
        );
        $accountNumber = preg_replace('/\D+/', '', $accountNumberRaw) ?? '';
        $maskedAccount = $this->maskAccountNumber($accountNumber);

        $viewData = [
            'employee_name' => $employeeName,
            'designation' => (string) (data_get($employee, 'designation') ?: $employeeUser?->position ?: ''),
            'department' => (string) (data_get($employee, 'department') ?: $employeeUser?->department ?: ''),
            'tax_station' => (string) data_get($employee, 'tax_station', ''),
            'account_number_masked' => $maskedAccount !== '' ? $maskedAccount : '',
            'date_of_joining' => $employeeUser?->join_date,
            'period_label' => Carbon::createFromDate((int) $payslip->period_year, (int) $payslip->period_month, 1)->format('M-y'),
            'worked_days' => (float) data_get($input, 'worked_days', 0),
            'total_working_days' => (float) data_get($input, 'total_working_days', 0),
            'daily_wage' => (float) data_get($computed, 'daily_wage', 0),
            'gross_salary' => (float) data_get($computed, 'total_taxable_earnings', data_get($input, 'monthly_gross', 0)),
            'total_taxable_earnings' => (float) data_get($computed, 'total_taxable_earnings', data_get($input, 'monthly_gross', 0)),
            'non_taxable_reimbursement' => (float) data_get($input, 'non_taxable_reimbursement', 0),
            'paye' => (float) data_get($computed, 'monthly_paye', 0),
            'unpaid_time_deduction' => (float) data_get($computed, 'unpaid_time_deduction', 0),
            'salary_advance' => (float) data_get($input, 'salary_advance', 0),
            'kpi_other_deductions' => (float) data_get($input, 'kpi_other_deductions', 0),
            'total_deductions' => (float) data_get($computed, 'total_deductions', 0),
            'net_salary' => (float) data_get($computed, 'net_salary', 0),
            'total_paid' => (float) data_get($computed, 'total_paid', 0),
            'annual_rent' => (float) data_get($input, 'annual_rent', 0),
            'rent_relief' => (float) data_get($computed, 'rent_relief', 0),
            'annual_taxable_income' => (float) data_get($computed, 'annual_taxable', 0),
            'paye_year' => (int) $payslip->period_year,
        ];

        $filename = sprintf('Payslip-%s-%04d-%02d.pdf', $employeeName, (int) $payslip->period_year, (int) $payslip->period_month);
        $filename = preg_replace('/[^A-Za-z0-9\-\._]/', '_', $filename) ?: 'Payslip.pdf';

        return Pdf::loadView('payroll.payslip-pdf', $viewData)
            ->setPaper('a4')
            ->download($filename);
    }

    private function maskAccountNumber(string $accountNumber): string
    {
        $digits = preg_replace('/\D+/', '', $accountNumber) ?? '';
        if ($digits === '') {
            return '';
        }

        $visible = min(4, strlen($digits));

        return str_repeat('*', max(0, strlen($digits) - $visible)).substr($digits, -$visible);
    }
}
