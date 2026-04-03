<?php

namespace App\Filament\Pages;

use App\Exports\ArrayReportExport;
use App\Support\PayrollTaxDueReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaxDueByStationReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 12;
    protected static ?string $title = 'Tax Due By Station';
    protected static ?string $slug = 'tax-due-by-station';

    protected static string $view = 'filament.pages.tax-due-by-station-report';

    public ?int $reportYear = null;
    public ?int $reportMonth = null;
    public string $selectedTaxStation = '';

    public function mount(): void
    {
        $latest = PayrollTaxDueReport::latestPeriod();
        $this->reportYear = $latest['year'] ?? (int) now()->year;
        $this->reportMonth = $latest['month'] ?? (int) now()->month;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->canViewReports() || $user->canManagePayroll());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getYears(): array
    {
        $years = PayrollTaxDueReport::availableYears();
        if ($years === []) {
            $year = (int) now()->year;
            return [$year => (string) $year];
        }

        return $years;
    }

    public function getMonths(): array
    {
        $year = (int) ($this->reportYear ?? 0);
        if ($year <= 0) {
            return [];
        }

        $months = PayrollTaxDueReport::availableMonthsForYear($year);
        if ($months === []) {
            return [(int) now()->month => date('F', mktime(0, 0, 0, (int) now()->month, 1))];
        }

        return $months;
    }

    public function updatedReportYear(): void
    {
        $months = $this->getMonths();
        $current = (int) ($this->reportMonth ?? 0);
        if ($current <= 0 || ! array_key_exists($current, $months)) {
            $first = array_key_first($months);
            $this->reportMonth = $first !== null ? (int) $first : null;
        }
    }

    public function getStationRows()
    {
        return PayrollTaxDueReport::forPeriod(
            (int) ($this->reportYear ?? now()->year),
            (int) ($this->reportMonth ?? now()->month),
        );
    }

    public function getStationOptions(): array
    {
        $rows = $this->getStationRows();
        $options = ['' => 'All Stations'];
        foreach ($rows as $row) {
            $options[$row['tax_station']] = $row['tax_station'];
        }

        return $options;
    }

    public function getEmployeeRows()
    {
        return PayrollTaxDueReport::employeeBreakdownForPeriod(
            (int) ($this->reportYear ?? now()->year),
            (int) ($this->reportMonth ?? now()->month),
            $this->selectedTaxStation !== '' ? $this->selectedTaxStation : null,
        );
    }

    public function exportStationSummary(string $format = 'csv')
    {
        [$headers, $rows] = $this->stationSummaryExportData();

        $suffix = $this->selectedTaxStation !== '' ? '-' . preg_replace('/[^A-Za-z0-9\-]+/', '-', strtolower($this->selectedTaxStation)) : '';
        $filename = sprintf('tax-station-summary-%d-%02d%s.%s', (int) $this->reportYear, (int) $this->reportMonth, $suffix, strtolower($format));

        return $this->buildExportResponse(
            title: sprintf('Tax Station Summary (%02d/%d)', (int) $this->reportMonth, (int) $this->reportYear),
            headers: $headers,
            rows: $rows,
            filename: $filename,
            format: $format
        );
    }

    public function exportEmployeeBreakdown(string $format = 'csv')
    {
        [$headers, $rows] = $this->employeeBreakdownExportData();

        $suffix = $this->selectedTaxStation !== '' ? '-' . preg_replace('/[^A-Za-z0-9\-]+/', '-', strtolower($this->selectedTaxStation)) : '';
        $filename = sprintf('tax-station-employees-%d-%02d%s.%s', (int) $this->reportYear, (int) $this->reportMonth, $suffix, strtolower($format));

        return $this->buildExportResponse(
            title: sprintf('Tax Employee Breakdown (%02d/%d)', (int) $this->reportMonth, (int) $this->reportYear),
            headers: $headers,
            rows: $rows,
            filename: $filename,
            format: $format
        );
    }

    private function stationSummaryExportData(): array
    {
        $rows = $this->getStationRows();
        $headers = ['Tax Station', 'Employees', 'PAYE Due (NGN)'];
        $data = $rows->map(fn (array $row): array => [
            $row['tax_station'],
            (int) $row['employee_count'],
            number_format((float) $row['paye_due'], 2, '.', ''),
        ])->all();

        return [$headers, $data];
    }

    private function employeeBreakdownExportData(): array
    {
        $rows = $this->getEmployeeRows();
        $headers = ['Employee', 'Employee ID', 'Tax Station', 'Run Code', 'PAYE (NGN)', 'Total Deductions (NGN)', 'Net Salary (NGN)'];
        $data = $rows->map(fn (array $row): array => [
            $row['employee_name'],
            $row['employee_id'],
            $row['tax_station'],
            $row['run_code'],
            number_format((float) $row['paye_due'], 2, '.', ''),
            number_format((float) $row['total_deductions'], 2, '.', ''),
            number_format((float) $row['net_salary'], 2, '.', ''),
        ])->all();

        return [$headers, $data];
    }

    private function buildExportResponse(string $title, array $headers, array $rows, string $filename, string $format)
    {
        $format = strtolower($format);
        if ($format === 'xlsx') {
            return Excel::download(new ArrayReportExport([$headers, ...$rows]), $filename);
        }

        if ($format === 'pdf') {
            return Pdf::loadView('exports.report-table', [
                'title' => $title,
                'headers' => $headers,
                'rows' => $rows,
            ])->download($filename);
        }

        if ($format !== 'csv') {
            Notification::make()
                ->title('Unsupported format')
                ->body('Please export as CSV, XLSX, or PDF.')
                ->danger()
                ->send();
            return null;
        }

        return $this->streamCsv($headers, $rows, $filename);
    }

    private function streamCsv(array $headers, array $rows, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
