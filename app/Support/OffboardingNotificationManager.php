<?php

namespace App\Support;

use App\Models\CompanySettings;
use App\Models\Employee;
use App\Models\EmployeeOffboarding;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OffboardingNotificationManager
{
    public static function sendStatusTransition(EmployeeOffboarding $offboarding, ?string $fromStatus, string $toStatus): bool
    {
        $toStatus = strtolower(trim($toStatus));
        if (! in_array($toStatus, ['planned', 'completed'], true)) {
            return false;
        }

        $settings = CompanySettings::current();
        if ($toStatus === 'planned' && ! (bool) $settings->offboarding_notify_on_planned) {
            return false;
        }
        if ($toStatus === 'completed' && ! (bool) $settings->offboarding_notify_on_completed) {
            return false;
        }

        $employee = User::query()->where('user_id', $offboarding->user_id)->first();
        $employeeName = (string) ($employee?->name ?: $offboarding->user_id);

        $subjectTemplate = $toStatus === 'planned'
            ? (string) ($settings->offboarding_planned_subject ?: 'Offboarding Initiated - {employee_name}')
            : (string) ($settings->offboarding_completed_subject ?: 'Offboarding Completed - {employee_name}');
        $bodyTemplate = $toStatus === 'planned'
            ? (string) ($settings->offboarding_planned_body ?: 'Employee {employee_name} ({employee_id}) has entered offboarding. Status: {from_status} -> {to_status}. Last working day: {last_working_day}. Type: {offboarding_type}. Reason: {offboarding_reason}.')
            : (string) ($settings->offboarding_completed_body ?: 'Offboarding for {employee_name} ({employee_id}) is complete. Completed by: {completed_by}. Completed at: {completed_at}.');

        $subject = self::render($subjectTemplate, $offboarding, $employee, $fromStatus, $toStatus);
        $body = self::render($bodyTemplate, $offboarding, $employee, $fromStatus, $toStatus);
        $recipients = self::resolveRecipients($offboarding, $employee);

        if ($recipients === []) {
            return false;
        }

        try {
            MailSettingsManager::apply();
            Mail::raw($body, function ($message) use ($recipients, $subject): void {
                $message->to(array_shift($recipients));
                if (! empty($recipients)) {
                    $message->cc($recipients);
                }
                $message->subject($subject);
            });

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return array<int, string>
     */
    private static function resolveRecipients(EmployeeOffboarding $offboarding, ?User $employee): array
    {
        $emails = [];

        if ($employee?->email) {
            $emails[] = trim((string) $employee->email);
        }

        $managerEmail = self::resolveManagerEmail($offboarding->user_id);
        if ($managerEmail !== null) {
            $emails[] = $managerEmail;
        }

        $roleRecipients = User::query()
            ->whereIn('role_name', ['Super Admin', 'Admin', 'HR Manager', 'Payroll Admin', 'Operations Manager'])
            ->whereNotNull('email')
            ->pluck('email')
            ->map(fn ($email) => trim((string) $email))
            ->filter()
            ->all();

        $emails = array_merge($emails, $roleRecipients);
        $emails = array_values(array_unique(array_filter($emails)));

        return $emails;
    }

    private static function resolveManagerEmail(string $userId): ?string
    {
        $reportsTo = Employee::query()
            ->where('employee_id', $userId)
            ->value('reports_to');

        $managerName = trim((string) $reportsTo);
        if ($managerName === '') {
            return null;
        }

        $manager = User::query()
            ->where('name', $managerName)
            ->whereNotNull('email')
            ->first();

        return $manager?->email ? trim((string) $manager->email) : null;
    }

    private static function render(
        string $template,
        EmployeeOffboarding $offboarding,
        ?User $employee,
        ?string $fromStatus,
        string $toStatus,
    ): string
    {
        $completedBy = User::query()->where('user_id', (string) $offboarding->completed_by_user_id)->value('name');

        $tokens = [
            '{employee_name}' => (string) ($employee?->name ?: $offboarding->user_id),
            '{employee_id}' => (string) $offboarding->user_id,
            '{from_status}' => str_replace('_', ' ', strtolower((string) ($fromStatus ?: 'not_started'))),
            '{to_status}' => str_replace('_', ' ', strtolower((string) $toStatus)),
            '{offboarding_type}' => $offboarding->offboarding_type ? str_replace('_', ' ', strtolower((string) $offboarding->offboarding_type)) : 'not set',
            '{last_working_day}' => $offboarding->last_working_day?->format('d M Y') ?: 'Not set',
            '{offboarding_reason}' => trim((string) ($offboarding->offboarding_reason ?: 'Not provided')),
            '{completed_by}' => (string) ($completedBy ?: $offboarding->completed_by_user_id ?: 'Not set'),
            '{completed_at}' => $offboarding->completed_at?->format('d M Y H:i') ?: 'Not set',
            '{company_name}' => (string) (CompanySettings::current()->company_name ?: 'Your Company'),
        ];

        return strtr($template, $tokens);
    }
}
