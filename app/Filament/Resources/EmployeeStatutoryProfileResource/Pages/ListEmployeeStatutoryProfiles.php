<?php

namespace App\Filament\Resources\EmployeeStatutoryProfileResource\Pages;

use App\Filament\Resources\EmployeeStatutoryProfileResource;
use App\Models\EmployeeStatutoryProfile;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListEmployeeStatutoryProfiles extends ListRecords
{
    protected static string $resource = EmployeeStatutoryProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('downloadTemplate')
                ->label('Download Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn (): StreamedResponse => $this->downloadTemplate()),
            Actions\Action::make('bulkImport')
                ->label('Bulk Import')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->form([
                    FileUpload::make('import_file')
                        ->label('Statutory CSV File')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                        ->disk('public')
                        ->directory('imports/statutory-profiles')
                        ->required()
                        ->helperText('Use the template columns. Employee ID must match existing user IDs (e.g. KH_0007).'),
                ])
                ->action(function (array $data): void {
                    $relativePath = trim((string) ($data['import_file'] ?? ''));
                    if ($relativePath === '') {
                        Notification::make()
                            ->title('Import file is required.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $absolutePath = Storage::disk('public')->path($relativePath);
                    if (! is_file($absolutePath)) {
                        Notification::make()
                            ->title('Uploaded file not found.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $result = $this->importCsv($absolutePath);
                    Storage::disk('public')->delete($relativePath);

                    $body = "Created: {$result['created']}, Updated: {$result['updated']}, Failed: {$result['failed']}";
                    if ($result['failed'] > 0 && $result['errors'] !== []) {
                        $body .= "\n" . implode("\n", array_slice($result['errors'], 0, 5));
                    }

                    Notification::make()
                        ->title('Statutory profile import completed')
                        ->body($body)
                        ->color($result['failed'] > 0 ? 'warning' : 'success')
                        ->send();
                }),
        ];
    }

    private function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'employee_id',
            'tax_station',
            'tax_residency_state',
            'salary_basis',
            'payment_type',
            'pension_enabled',
            'employee_pension_rate_percent',
            'employer_pension_rate_percent',
            'pension_pin',
            'nhf_enabled',
            'nhf_rate_percent',
            'nhf_base_cap',
            'nhf_number',
            'annual_rent',
            'other_statutory_deductions',
            'default_non_taxable_reimbursement',
        ];

        $sample = [
            'KH_0007',
            'Lagos',
            'Lagos',
            'monthly',
            'bank_transfer',
            '1',
            '8.0',
            '10.0',
            'PEN1234567890',
            '1',
            '2.5',
            '',
            'NHF-0000123',
            '200000',
            '0',
            '0',
        ];

        return response()->streamDownload(function () use ($headers, $sample): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, $sample);
            fclose($handle);
        }, 'employee-statutory-profile-template.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @return array{created:int,updated:int,failed:int,errors:array<int,string>}
     */
    private function importCsv(string $absolutePath): array
    {
        $handle = fopen($absolutePath, 'r');
        if (! $handle) {
            return ['created' => 0, 'updated' => 0, 'failed' => 1, 'errors' => ['Unable to open CSV file.']];
        }

        $header = fgetcsv($handle);
        if (! is_array($header) || $header === []) {
            fclose($handle);
            return ['created' => 0, 'updated' => 0, 'failed' => 1, 'errors' => ['CSV header row is missing.']];
        }

        $normalized = array_map(fn ($value): string => $this->normalizeHeader((string) $value), $header);
        $map = array_flip($normalized);

        $employeeKey = null;
        foreach (['employee_id', 'user_id'] as $candidate) {
            if (array_key_exists($candidate, $map)) {
                $employeeKey = $candidate;
                break;
            }
        }

        if (! $employeeKey) {
            fclose($handle);
            return ['created' => 0, 'updated' => 0, 'failed' => 1, 'errors' => ['CSV must include employee_id column.']];
        }

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if (! is_array($row)) {
                continue;
            }

            $values = [];
            foreach ($normalized as $index => $key) {
                $values[$key] = isset($row[$index]) ? trim((string) $row[$index]) : '';
            }

            if ($this->isRowEmpty($values)) {
                continue;
            }

            $employeeId = trim((string) ($values[$employeeKey] ?? ''));
            if ($employeeId === '') {
                $failed++;
                $errors[] = "Row {$rowNumber}: employee_id is required.";
                continue;
            }

            $userExists = User::query()->where('user_id', $employeeId)->exists();
            if (! $userExists) {
                $failed++;
                $errors[] = "Row {$rowNumber}: employee_id {$employeeId} was not found.";
                continue;
            }

            $payload = [
                'tax_station' => $this->nullIfEmpty($values['tax_station'] ?? null),
                'tax_residency_state' => $this->nullIfEmpty($values['tax_residency_state'] ?? null),
                'salary_basis' => $this->inAllowed($values['salary_basis'] ?? null, ['hourly', 'daily', 'weekly', 'monthly']),
                'payment_type' => $this->inAllowed($values['payment_type'] ?? null, ['bank_transfer', 'cash', 'cheque']),
                'pension_enabled' => $this->toBool($values['pension_enabled'] ?? null, true),
                'employee_pension_rate_percent' => $this->toNumberOrNull($values['employee_pension_rate_percent'] ?? null, 8),
                'employer_pension_rate_percent' => $this->toNumberOrNull($values['employer_pension_rate_percent'] ?? null, 10),
                'pension_pin' => $this->nullIfEmpty($values['pension_pin'] ?? null),
                'nhf_enabled' => $this->toBool($values['nhf_enabled'] ?? null, false),
                'nhf_rate_percent' => $this->toNumberOrNull($values['nhf_rate_percent'] ?? null, 2.5),
                'nhf_base_cap' => $this->toNumberOrNull($values['nhf_base_cap'] ?? null, null),
                'nhf_number' => $this->nullIfEmpty($values['nhf_number'] ?? null),
                'annual_rent' => $this->toNumberOrNull($values['annual_rent'] ?? null, 0),
                'other_statutory_deductions' => $this->toNumberOrNull($values['other_statutory_deductions'] ?? null, 0),
                'default_non_taxable_reimbursement' => $this->toNumberOrNull($values['default_non_taxable_reimbursement'] ?? null, 0),
                'created_by_user_id' => Auth::user()?->user_id,
            ];

            $existing = EmployeeStatutoryProfile::query()->where('user_id', $employeeId)->first();
            if ($existing) {
                $existing->fill($payload)->save();
                $updated++;
            } else {
                EmployeeStatutoryProfile::query()->create(array_merge($payload, ['user_id' => $employeeId]));
                $created++;
            }
        }

        fclose($handle);

        return compact('created', 'updated', 'failed', 'errors');
    }

    private function normalizeHeader(string $header): string
    {
        $header = strtolower(trim($header));
        $header = preg_replace('/[^a-z0-9]+/', '_', $header) ?: '';
        return trim($header, '_');
    }

    private function isRowEmpty(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }
        return true;
    }

    private function nullIfEmpty(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function toBool(?string $value, bool $default): bool
    {
        $value = strtolower(trim((string) $value));
        if ($value === '') {
            return $default;
        }
        return in_array($value, ['1', 'true', 'yes', 'y', 'on'], true);
    }

    private function toNumberOrNull(?string $value, float|int|null $default): float|int|null
    {
        $value = trim((string) $value);
        if ($value === '') {
            return $default;
        }

        $normalized = str_replace([',', '₦', 'NGN', 'ngn', ' '], '', $value);
        if (! is_numeric($normalized)) {
            return $default;
        }

        return (float) $normalized;
    }

    private function inAllowed(?string $value, array $allowed): ?string
    {
        $value = strtolower(trim((string) $value));
        if ($value === '' || ! in_array($value, $allowed, true)) {
            return null;
        }
        return $value;
    }
}
