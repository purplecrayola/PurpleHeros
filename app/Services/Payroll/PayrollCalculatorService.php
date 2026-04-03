<?php

namespace App\Services\Payroll;

class PayrollCalculatorService
{
    public function calculate(array $input, array $policy): array
    {
        $monthlyGross = (float) ($input['monthly_gross'] ?? 0);
        $salaryAdvance = (float) ($input['salary_advance'] ?? 0);
        $kpiOther = (float) ($input['kpi_other_deductions'] ?? 0);
        $otherStatutory = (float) ($input['other_statutory_deductions'] ?? 0);
        $annualRent = (float) ($input['annual_rent'] ?? 0);
        $reimbursement = (float) ($input['non_taxable_reimbursement'] ?? 0);
        $unpaidDays = (float) ($input['unpaid_days'] ?? 0);
        $totalWorkingDays = (float) ($input['total_working_days'] ?? 0);

        $annualGross = $monthlyGross * 12;

        $rentReliefPercent = (float) ($policy['rent_relief_percent'] ?? 20);
        $rentReliefCap = (float) ($policy['rent_relief_cap'] ?? 500000);
        $rentRelief = min($annualRent * ($rentReliefPercent / 100), $rentReliefCap);

        $taxFreeThreshold = (float) ($policy['tax_free_threshold'] ?? 800000);
        $annualTaxable = max(0, $annualGross - $taxFreeThreshold - $rentRelief);

        $manualPayeOverride = isset($input['manual_paye_override']) ? (float) $input['manual_paye_override'] : null;
        if ($manualPayeOverride !== null && $manualPayeOverride > 0) {
            $monthlyPaye = $manualPayeOverride;
            $annualPaye = $monthlyPaye * 12;
        } else {
            $annualPaye = $this->calculateAnnualPaye($annualTaxable, $policy['paye_bands'] ?? []);
            $monthlyPaye = $annualPaye / 12;
        }

        $dailyWage = $totalWorkingDays > 0 ? ($monthlyGross / $totalWorkingDays) : 0;
        $unpaidTimeDeduction = $dailyWage * $unpaidDays;

        $isPensionEnabled = (bool) ($input['pension_enabled'] ?? ($policy['default_pension_enabled'] ?? true));
        $employeePensionRate = (float) ($input['employee_pension_rate_percent'] ?? ($policy['default_employee_pension_rate_percent'] ?? 8));
        $pensionBase = (float) ($input['pensionable_monthly_earnings'] ?? $monthlyGross);
        $monthlyPension = $isPensionEnabled ? ($pensionBase * ($employeePensionRate / 100)) : 0;

        $isNhfEnabled = (bool) ($input['nhf_enabled'] ?? ($policy['default_nhf_enabled'] ?? false));
        $nhfRate = (float) ($input['nhf_rate_percent'] ?? ($policy['default_nhf_rate_percent'] ?? 2.5));
        $nhfCap = isset($input['nhf_base_cap']) ? (float) $input['nhf_base_cap'] : null;
        $nhfBase = $nhfCap !== null && $nhfCap > 0 ? min($monthlyGross, $nhfCap) : $monthlyGross;
        $monthlyNhf = $isNhfEnabled ? ($nhfBase * ($nhfRate / 100)) : 0;

        $totalTaxableEarnings = $monthlyGross;
        $totalDeductions = $monthlyPaye + $monthlyPension + $monthlyNhf + $unpaidTimeDeduction + $salaryAdvance + $kpiOther + $otherStatutory;
        $netSalary = $totalTaxableEarnings - $totalDeductions;
        $totalPaid = $netSalary + $reimbursement;

        return [
            'annual_gross' => $this->money($annualGross),
            'rent_relief' => $this->money($rentRelief),
            'tax_free_threshold' => $this->money($taxFreeThreshold),
            'annual_taxable' => $this->money($annualTaxable),
            'annual_paye' => $this->money($annualPaye),
            'monthly_paye' => $this->money($monthlyPaye),
            'monthly_pension' => $this->money($monthlyPension),
            'monthly_nhf' => $this->money($monthlyNhf),
            'other_statutory_deductions' => $this->money($otherStatutory),
            'daily_wage' => $this->money($dailyWage),
            'unpaid_time_deduction' => $this->money($unpaidTimeDeduction),
            'total_taxable_earnings' => $this->money($totalTaxableEarnings),
            'total_deductions' => $this->money($totalDeductions),
            'net_salary' => $this->money($netSalary),
            'total_paid' => $this->money($totalPaid),
        ];
    }

    private function calculateAnnualPaye(float $annualTaxable, array $bands): float
    {
        if ($annualTaxable <= 0) {
            return 0;
        }

        if (empty($bands)) {
            $bands = [
                ['up_to' => 2200000, 'rate' => 15],
                ['up_to' => 11200000, 'rate' => 18],
                ['up_to' => 24200000, 'rate' => 21],
                ['up_to' => 49200000, 'rate' => 23],
                ['up_to' => null, 'rate' => 25],
            ];
        }

        $remaining = $annualTaxable;
        $lowerBound = 0;
        $tax = 0;

        foreach ($bands as $band) {
            $rate = ((float) ($band['rate'] ?? 0)) / 100;
            $upper = $band['up_to'] ?? null;

            if ($upper === null) {
                $tax += $remaining * $rate;
                break;
            }

            $bandWidth = max(0, ((float) $upper) - $lowerBound);
            $taxableInBand = min($remaining, $bandWidth);

            if ($taxableInBand <= 0) {
                break;
            }

            $tax += $taxableInBand * $rate;
            $remaining -= $taxableInBand;
            $lowerBound = (float) $upper;

            if ($remaining <= 0) {
                break;
            }
        }

        return $tax;
    }

    private function money(float $value): float
    {
        return round($value, 2);
    }
}
