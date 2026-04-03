<?php

namespace App\Services\Payroll;

use App\Models\EmployeeStatutoryProfile;
use App\Models\BankInformation;
use App\Models\PayrollLineItem;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\StaffSalary;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PayrollRunGeneratorService
{
    public function __construct(private readonly PayrollCalculatorService $calculator)
    {
    }

    public function generate(PayrollRun $run, ?array $userIds = null): array
    {
        $run->loadMissing('policySet');

        $policy = $run->policySet?->resolvedSettings() ?? [
            'tax_free_threshold' => 800000,
            'rent_relief_percent' => 20,
            'rent_relief_cap' => 500000,
            'paye_bands' => [
                ['up_to' => 2200000, 'rate' => 15],
                ['up_to' => 11200000, 'rate' => 18],
                ['up_to' => 24200000, 'rate' => 21],
                ['up_to' => 49200000, 'rate' => 23],
                ['up_to' => null, 'rate' => 25],
            ],
        ];

        $salaryQuery = StaffSalary::query()->whereNotNull('user_id');

        if (! empty($userIds)) {
            $salaryQuery->whereIn('user_id', $userIds);
        }

        $salaries = $salaryQuery->get();
        $users = User::query()->whereIn('user_id', $salaries->pluck('user_id')->all())->get()->keyBy('user_id');
        $profiles = EmployeeStatutoryProfile::query()
            ->whereIn('user_id', $salaries->pluck('user_id')->all())
            ->get()
            ->keyBy('user_id');
        $bankProfiles = BankInformation::query()
            ->whereIn('user_id', $salaries->pluck('user_id')->all())
            ->get()
            ->keyBy('user_id');

        $generated = 0;

        DB::transaction(function () use ($run, $salaries, $users, $profiles, $bankProfiles, $policy, &$generated): void {
            foreach ($salaries as $salary) {
                $user = $users->get($salary->user_id);
                $profile = $profiles->get($salary->user_id);
                $bankProfile = $bankProfiles->get($salary->user_id);
                $accountNumber = (string) (
                    $bankProfile?->primary_bank_account_no
                    ?: $bankProfile?->bank_account_no
                    ?: $bankProfile?->secondary_bank_account_no
                    ?: ''
                );

                $configuredGross = (float) ($salary->salary ?? 0);
                $legacyComponentGross = (float) ($salary->basic ?? 0)
                    + (float) ($salary->da ?? 0)
                    + (float) ($salary->hra ?? 0)
                    + (float) ($salary->conveyance ?? 0)
                    + (float) ($salary->allowance ?? 0)
                    + (float) ($salary->medical_allowance ?? 0);

                $monthlyGross = $configuredGross > 0 ? $configuredGross : $legacyComponentGross;
                $workedDays = (int) ($salary->worked_days ?? $run->default_worked_days);
                $totalWorkingDays = (int) ($salary->total_working_days ?? $run->default_total_working_days);
                $unpaidDays = (float) ($salary->unpaid_days ?? 0);
                $salaryAdvance = (float) ($salary->salary_advance ?? $salary->labour_welfare ?? 0);
                $kpiOtherDeductions = (float) ($salary->kpi_other_deductions ?? $salary->leave ?? 0);
                $annualRent = (float) ($salary->annual_rent ?? $profile?->annual_rent ?? 0);
                $nonTaxableReimbursement = (float) ($salary->non_taxable_reimbursement ?? $profile?->default_non_taxable_reimbursement ?? 0);
                $legacyOtherStatutory = (float) ($salary->esi ?? 0) + (float) ($salary->pf ?? 0) + (float) ($salary->prof_tax ?? 0);
                $manualPayeOverride = (float) ($salary->tds ?? 0);

                $pensionEnabled = $profile?->pension_enabled;
                if ($pensionEnabled === null) {
                    $pensionEnabled = (bool) ($policy['default_pension_enabled'] ?? true);
                }

                $nhfEnabled = $profile?->nhf_enabled;
                if ($nhfEnabled === null) {
                    $nhfEnabled = (bool) ($policy['default_nhf_enabled'] ?? false);
                }

                $input = [
                    'monthly_gross' => $monthlyGross,
                    'worked_days' => $workedDays,
                    'total_working_days' => $totalWorkingDays,
                    'unpaid_days' => $unpaidDays,
                    'salary_advance' => $salaryAdvance,
                    'kpi_other_deductions' => $kpiOtherDeductions,
                    'annual_rent' => $annualRent,
                    'non_taxable_reimbursement' => $nonTaxableReimbursement,
                    'other_statutory_deductions' => (float) ($profile?->other_statutory_deductions ?? 0) + $legacyOtherStatutory,
                    'manual_paye_override' => $manualPayeOverride > 0 ? $manualPayeOverride : null,
                    'pension_enabled' => (bool) $pensionEnabled,
                    'employee_pension_rate_percent' => $profile?->employee_pension_rate_percent ?? ($policy['default_employee_pension_rate_percent'] ?? 8),
                    'nhf_enabled' => (bool) $nhfEnabled,
                    'nhf_rate_percent' => $profile?->nhf_rate_percent ?? ($policy['default_nhf_rate_percent'] ?? 2.5),
                    'nhf_base_cap' => $profile?->nhf_base_cap,
                ];

                $computed = $this->calculator->calculate($input, $policy);

                $runEmployee = $run->employees()->updateOrCreate(
                    ['user_id' => (string) $salary->user_id],
                    [
                        'employee_name' => (string) ($salary->name ?: ($user?->name ?? $salary->user_id)),
                        'designation' => $user?->position,
                        'department' => $user?->department,
                        'tax_station' => $salary->tax_station ?: ($profile?->tax_station ?: ($profile?->tax_residency_state ?: ($run->policySet?->state_code ?: null))),
                        'account_number' => $accountNumber !== '' ? $accountNumber : null,
                        'source' => 'generated',
                        'status' => 'draft',
                        'input_payload' => $input,
                        'computed_payload' => $computed,
                        'gross_salary' => $computed['total_taxable_earnings'],
                        'total_taxable_earnings' => $computed['total_taxable_earnings'],
                        'total_deductions' => $computed['total_deductions'],
                        'net_salary' => $computed['net_salary'],
                        'total_paid' => $computed['total_paid'],
                    ]
                );

                $runEmployee->lineItems()->delete();

                $this->createLineItems($runEmployee->id, $input, $computed);

                Payslip::query()->updateOrCreate(
                    [
                        'payroll_run_id' => $run->id,
                        'user_id' => (string) $salary->user_id,
                        'period_year' => (int) $run->period_year,
                        'period_month' => (int) $run->period_month,
                    ],
                    [
                        'payroll_run_employee_id' => $runEmployee->id,
                        'source' => 'generated',
                        'payload' => [
                            'employee' => Arr::only($runEmployee->toArray(), ['employee_name', 'designation', 'department', 'tax_station', 'account_number']),
                            'input' => $input,
                            'computed' => $computed,
                        ],
                        'issued_at' => now(),
                    ]
                );

                $generated++;
            }

            $run->update([
                'status' => 'calculated',
                'calculated_at' => now(),
            ]);
        });

        return [
            'generated' => $generated,
            'run_id' => $run->id,
        ];
    }

    private function createLineItems(int $runEmployeeId, array $input, array $computed): void
    {
        $lineItems = [
            ['line_type' => 'earning', 'code' => 'GROSS', 'label' => 'Gross Salary', 'is_taxable' => true, 'amount' => $input['monthly_gross'], 'sort_order' => 10],
            ['line_type' => 'earning', 'code' => 'TAXABLE_TOTAL', 'label' => 'Total Taxable Earnings', 'is_taxable' => true, 'amount' => $computed['total_taxable_earnings'], 'sort_order' => 20],
            ['line_type' => 'reimbursement', 'code' => 'REIMBURSEMENT', 'label' => 'Out-of-Hours / Work Expense Reimbursement', 'is_taxable' => false, 'amount' => $input['non_taxable_reimbursement'], 'sort_order' => 30],
            ['line_type' => 'deduction', 'code' => 'PAYE', 'label' => 'PAYE', 'is_taxable' => false, 'amount' => $computed['monthly_paye'], 'sort_order' => 40],
            ['line_type' => 'deduction', 'code' => 'PENSION', 'label' => 'Pension (Employee)', 'is_taxable' => false, 'amount' => $computed['monthly_pension'], 'sort_order' => 50],
            ['line_type' => 'deduction', 'code' => 'NHF', 'label' => 'NHF', 'is_taxable' => false, 'amount' => $computed['monthly_nhf'], 'sort_order' => 60],
            ['line_type' => 'deduction', 'code' => 'STAT_OTHER', 'label' => 'Other Statutory Deductions', 'is_taxable' => false, 'amount' => $computed['other_statutory_deductions'], 'sort_order' => 70],
            ['line_type' => 'deduction', 'code' => 'UNPAID_TIME', 'label' => 'Unpaid Time Off Deduction', 'is_taxable' => false, 'amount' => $computed['unpaid_time_deduction'], 'sort_order' => 80],
            ['line_type' => 'deduction', 'code' => 'SALARY_ADVANCE', 'label' => 'Salary Advance', 'is_taxable' => false, 'amount' => $input['salary_advance'], 'sort_order' => 90],
            ['line_type' => 'deduction', 'code' => 'KPI_OTHER', 'label' => 'KPI / Other Deductions', 'is_taxable' => false, 'amount' => $input['kpi_other_deductions'], 'sort_order' => 100],
            ['line_type' => 'deduction', 'code' => 'DEDUCTIONS_TOTAL', 'label' => 'Total Deductions', 'is_taxable' => false, 'amount' => $computed['total_deductions'], 'sort_order' => 110],
            ['line_type' => 'earning', 'code' => 'NET_SALARY', 'label' => 'Net Salary', 'is_taxable' => false, 'amount' => $computed['net_salary'], 'sort_order' => 120],
            ['line_type' => 'earning', 'code' => 'TOTAL_PAID', 'label' => 'Total Paid', 'is_taxable' => false, 'amount' => $computed['total_paid'], 'sort_order' => 130],
        ];

        foreach ($lineItems as $lineItem) {
            PayrollLineItem::query()->create(array_merge($lineItem, ['payroll_run_employee_id' => $runEmployeeId]));
        }
    }
}
