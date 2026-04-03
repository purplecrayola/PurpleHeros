<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip</title>
    <style>
        @page { margin: 20px; }
        body { font-family: DejaVu Sans, sans-serif; color: #101828; font-size: 12px; }
        .brand { display: table; width: 100%; margin-bottom: 8px; }
        .brand-mark { display: table-cell; width: 54px; height: 54px; border-radius: 12px; background: #6f2dd6; color: #fff; font-weight: bold; text-align: center; vertical-align: middle; font-size: 22px; }
        .brand-text { display: table-cell; vertical-align: middle; padding-left: 10px; font-size: 30px; line-height: 1; font-weight: 700; color: #2b2f38; }
        .title { background: #6f2dd6; color: #fff; text-align: center; padding: 8px 0; font-size: 34px; letter-spacing: 2px; margin-bottom: 0; }
        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid #222; padding: 4px 6px; vertical-align: middle; }
        .label { width: 26%; }
        .value { width: 24%; text-align: right; }
        .section { background: #6f2dd6; color: #fff; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; }
        .amount { text-align: right; }
        .footnote { margin-top: 16px; text-align: center; color: #6b7280; font-style: italic; font-size: 13px; }
    </style>
</head>
<body>
    <div class="brand">
        <div class="brand-mark">P</div>
        <div class="brand-text">Purple<br>Crayola.</div>
    </div>

    <div class="title">PAYSLIP</div>

    <table>
        <tr>
            <td class="label">Select Employee Name</td>
            <td colspan="3">{{ $employee_name }}</td>
        </tr>
        <tr>
            <td class="label">Date of Joining</td>
            <td class="value">{{ $date_of_joining ? \Carbon\Carbon::parse($date_of_joining)->format('d/m/Y') : '' }}</td>
            <td class="label">Employee name</td>
            <td class="value">{{ $employee_name }}</td>
        </tr>
        <tr>
            <td class="label">Pay Period</td>
            <td class="value">{{ $period_label }}</td>
            <td class="label">Designation</td>
            <td class="value">{{ $designation }}</td>
        </tr>
        <tr>
            <td class="label">Worked Days</td>
            <td class="value">{{ (int) round($worked_days) }}</td>
            <td class="label">Department</td>
            <td class="value">{{ $department }}</td>
        </tr>
        <tr>
            <td class="label">Total Working Days</td>
            <td class="value">{{ (int) round($total_working_days) }}</td>
            <td class="label">Tax Station</td>
            <td class="value">{{ $tax_station }}</td>
        </tr>
        <tr>
            <td class="label">Period Daily Wage</td>
            <td class="value">{{ number_format((float) $daily_wage, 6) }}</td>
            <td class="label">Account No (masked)</td>
            <td class="value">{{ $account_number_masked }}</td>
        </tr>
    </table>

    <table>
        <tr><td class="section" colspan="4">Earnings (Taxable)</td></tr>
        <tr>
            <td colspan="3">Gross Salary</td>
            <td class="amount">{{ number_format((float) $gross_salary, 2) }}</td>
        </tr>
        <tr>
            <td colspan="3">Total Taxable Earnings</td>
            <td class="amount">{{ number_format((float) $total_taxable_earnings, 2) }}</td>
        </tr>

        <tr><td class="section" colspan="4">Other Payments (Non-Taxable)</td></tr>
        <tr>
            <td colspan="3">Out-of-Hours / Work Expense Reimbursement</td>
            <td class="amount">{{ number_format((float) $non_taxable_reimbursement, 2) }}</td>
        </tr>

        <tr><td class="section" colspan="4">Deductions</td></tr>
        <tr>
            <td colspan="3">PAYE ({{ $paye_year }})</td>
            <td class="amount">{{ number_format((float) $paye, 2) }}</td>
        </tr>
        <tr>
            <td colspan="3">Unpaid Time Off Deduction</td>
            <td class="amount">{{ number_format((float) $unpaid_time_deduction, 2) }}</td>
        </tr>
        <tr>
            <td colspan="3">Salary Advance</td>
            <td class="amount">{{ number_format((float) $salary_advance, 2) }}</td>
        </tr>
        <tr>
            <td colspan="3">KPI / Other Deductions</td>
            <td class="amount">{{ number_format((float) $kpi_other_deductions, 2) }}</td>
        </tr>
        <tr>
            <td colspan="3">Total Deductions</td>
            <td class="amount">{{ number_format((float) $total_deductions, 2) }}</td>
        </tr>

        <tr><td class="section" colspan="4">Net Summary</td></tr>
        <tr>
            <td colspan="3">Net Salary</td>
            <td class="amount">{{ number_format((float) $net_salary, 2) }}</td>
        </tr>
        <tr>
            <td colspan="3">Total Paid</td>
            <td class="amount">{{ number_format((float) $total_paid, 2) }}</td>
        </tr>

        <tr><td class="section" colspan="4">Tax Basis (For Record)</td></tr>
        <tr>
            <td colspan="3">Annual Rent (Declared)</td>
            <td class="amount">{{ number_format((float) $annual_rent, 2) }}</td>
        </tr>
        <tr>
            <td colspan="3">Rent Relief (20%, cap ₦500k)</td>
            <td class="amount">{{ number_format((float) $rent_relief, 2) }}</td>
        </tr>
        <tr>
            <td colspan="3">Annual Taxable Income</td>
            <td class="amount">{{ number_format((float) $annual_taxable_income, 2) }}</td>
        </tr>
    </table>

    <div class="footnote">This is system generated payslip</div>
</body>
</html>
