<?php

namespace App\Support;

use App\Models\PayrollRun;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayrollTaxDueReport
{
    /**
     * @return array{year:int,month:int}|null
     */
    public static function latestPeriod(): ?array
    {
        $latest = PayrollRun::query()
            ->whereIn('status', ['calculated', 'approved', 'posted', 'locked'])
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->first(['period_year', 'period_month']);

        if (! $latest) {
            return null;
        }

        return [
            'year' => (int) $latest->period_year,
            'month' => (int) $latest->period_month,
        ];
    }

    /**
     * @return Collection<int, array{tax_station:string,employee_count:int,paye_due:float}>
     */
    public static function forPeriod(int $year, int $month): Collection
    {
        $stationExpr = "COALESCE(NULLIF(TRIM(payroll_run_employees.tax_station), ''), 'Unassigned')";

        return DB::table('payroll_run_employees')
            ->join('payroll_runs', 'payroll_runs.id', '=', 'payroll_run_employees.payroll_run_id')
            ->leftJoin('payroll_line_items as paye_items', function ($join): void {
                $join->on('paye_items.payroll_run_employee_id', '=', 'payroll_run_employees.id')
                    ->where('paye_items.code', '=', 'PAYE');
            })
            ->where('payroll_runs.period_year', $year)
            ->where('payroll_runs.period_month', $month)
            ->whereIn('payroll_runs.status', ['calculated', 'approved', 'posted', 'locked'])
            ->selectRaw("{$stationExpr} as tax_station")
            ->selectRaw('COUNT(DISTINCT payroll_run_employees.id) as employee_count')
            ->selectRaw('ROUND(SUM(COALESCE(paye_items.amount, 0)), 2) as paye_due')
            ->groupBy(DB::raw($stationExpr))
            ->orderBy(DB::raw($stationExpr))
            ->get()
            ->map(fn ($row): array => [
                'tax_station' => (string) ($row->tax_station ?? 'Unassigned'),
                'employee_count' => (int) ($row->employee_count ?? 0),
                'paye_due' => (float) ($row->paye_due ?? 0),
            ]);
    }

    /**
     * @return Collection<int, array{employee_id:string,employee_name:string,tax_station:string,paye_due:float,total_deductions:float,net_salary:float,run_code:string}>
     */
    public static function employeeBreakdownForPeriod(int $year, int $month, ?string $taxStation = null): Collection
    {
        $stationExpr = "COALESCE(NULLIF(TRIM(payroll_run_employees.tax_station), ''), 'Unassigned')";

        $query = DB::table('payroll_run_employees')
            ->join('payroll_runs', 'payroll_runs.id', '=', 'payroll_run_employees.payroll_run_id')
            ->leftJoin('payroll_line_items as paye_items', function ($join): void {
                $join->on('paye_items.payroll_run_employee_id', '=', 'payroll_run_employees.id')
                    ->where('paye_items.code', '=', 'PAYE');
            })
            ->where('payroll_runs.period_year', $year)
            ->where('payroll_runs.period_month', $month)
            ->whereIn('payroll_runs.status', ['calculated', 'approved', 'posted', 'locked'])
            ->selectRaw('payroll_run_employees.user_id as employee_id')
            ->selectRaw('payroll_run_employees.employee_name as employee_name')
            ->selectRaw("{$stationExpr} as tax_station")
            ->selectRaw('COALESCE(paye_items.amount, 0) as paye_due')
            ->selectRaw('COALESCE(payroll_run_employees.total_deductions, 0) as total_deductions')
            ->selectRaw('COALESCE(payroll_run_employees.net_salary, 0) as net_salary')
            ->selectRaw('payroll_runs.run_code as run_code');

        if (filled($taxStation)) {
            $query->whereRaw("{$stationExpr} = ?", [$taxStation]);
        }

        return $query
            ->orderByRaw($stationExpr)
            ->orderBy('payroll_run_employees.employee_name')
            ->get()
            ->map(fn ($row): array => [
                'employee_id' => (string) ($row->employee_id ?? ''),
                'employee_name' => (string) ($row->employee_name ?? 'Unknown Employee'),
                'tax_station' => (string) ($row->tax_station ?? 'Unassigned'),
                'paye_due' => (float) ($row->paye_due ?? 0),
                'total_deductions' => (float) ($row->total_deductions ?? 0),
                'net_salary' => (float) ($row->net_salary ?? 0),
                'run_code' => (string) ($row->run_code ?? ''),
            ]);
    }

    /**
     * @return Collection<int, array{period_year:int,period_month:int,label:string,station_count:int,paye_due_total:float}>
     */
    public static function recentMonthlyTotals(int $months = 6): Collection
    {
        $periods = PayrollRun::query()
            ->whereIn('status', ['calculated', 'approved', 'posted', 'locked'])
            ->select('period_year', 'period_month')
            ->distinct()
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->limit(max(1, $months))
            ->get();

        return $periods->map(function ($period): array {
            $year = (int) $period->period_year;
            $month = (int) $period->period_month;
            $rows = self::forPeriod($year, $month);

            return [
                'period_year' => $year,
                'period_month' => $month,
                'label' => date('M Y', mktime(0, 0, 0, $month, 1, $year)),
                'station_count' => $rows->count(),
                'paye_due_total' => round((float) $rows->sum('paye_due'), 2),
            ];
        });
    }

    /**
     * @return array<int, string>
     */
    public static function availableYears(): array
    {
        return PayrollRun::query()
            ->whereIn('status', ['calculated', 'approved', 'posted', 'locked'])
            ->select('period_year')
            ->distinct()
            ->orderByDesc('period_year')
            ->pluck('period_year', 'period_year')
            ->mapWithKeys(fn ($value, $key): array => [(int) $key => (string) $value])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function availableMonthsForYear(int $year): array
    {
        $months = PayrollRun::query()
            ->whereIn('status', ['calculated', 'approved', 'posted', 'locked'])
            ->where('period_year', $year)
            ->select('period_month')
            ->distinct()
            ->orderByDesc('period_month')
            ->pluck('period_month')
            ->map(fn ($month): int => (int) $month)
            ->all();

        $options = [];
        foreach ($months as $month) {
            $options[$month] = date('F', mktime(0, 0, 0, $month, 1));
        }

        return $options;
    }
}
