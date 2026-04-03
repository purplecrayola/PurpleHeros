<?php

namespace App\Support;

use App\Models\CompanySettings;
use App\Models\LearningEnrollment;
use Illuminate\Support\Facades\Mail;
use Throwable;

class LearningNotificationManager
{
    public static function sendAssignmentNotification(LearningEnrollment $enrollment): bool
    {
        $settings = CompanySettings::current();
        if (! (bool) $settings->learning_notify_on_assignment) {
            return false;
        }

        return self::sendWithTemplate(
            $enrollment,
            (string) ($settings->learning_assignment_subject ?: 'New Learning Assignment: {course_title}'),
            (string) ($settings->learning_assignment_body ?: 'Hello {employee_name}, you have been enrolled in {course_title}. Due date: {due_date}.')
        );
    }

    public static function sendReminderNotification(LearningEnrollment $enrollment, ?string $customMessage = null): bool
    {
        $settings = CompanySettings::current();
        if (! (bool) $settings->learning_notify_on_reminder) {
            return false;
        }

        $subject = (string) ($settings->learning_reminder_subject ?: 'Learning Reminder: {course_title}');
        $body = trim((string) ($customMessage ?? ''));
        if ($body === '') {
            $body = (string) ($settings->learning_reminder_body ?: 'Hello {employee_name}, this is a reminder to complete {course_title} by {due_date}.');
        }

        return self::sendWithTemplate($enrollment, $subject, $body);
    }

    public static function sendCompletionNotification(LearningEnrollment $enrollment, ?string $customMessage = null): bool
    {
        $settings = CompanySettings::current();
        if (! (bool) $settings->learning_notify_on_completion) {
            return false;
        }

        $subject = (string) ($settings->learning_completion_subject ?: 'Learning Completed: {course_title}');
        $body = trim((string) ($customMessage ?? ''));
        if ($body === '') {
            $body = (string) ($settings->learning_completion_body ?: 'Hello {employee_name}, completion has been recorded for {course_title}. Great work.');
        }

        return self::sendWithTemplate($enrollment, $subject, $body);
    }

    public static function render(string $template, LearningEnrollment $enrollment): string
    {
        $user = $enrollment->user;
        $course = $enrollment->course;

        $tokens = [
            '{employee_name}' => (string) ($user->name ?? 'Team Member'),
            '{employee_id}' => (string) ($user->user_id ?? ''),
            '{employee_email}' => (string) ($user->email ?? ''),
            '{course_title}' => (string) ($course->title ?? 'Course'),
            '{course_code}' => (string) ($course->course_code ?? ''),
            '{course_mode}' => (string) ($course->delivery_mode ?? ''),
            '{due_date}' => $enrollment->due_at ? $enrollment->due_at->format('d M Y') : 'No due date',
            '{completion_percent}' => number_format((float) $enrollment->completion_percent, 1) . '%',
            '{company_name}' => (string) (CompanySettings::current()->company_name ?: 'Your Company'),
        ];

        return strtr($template, $tokens);
    }

    private static function sendWithTemplate(LearningEnrollment $enrollment, string $subjectTemplate, string $bodyTemplate): bool
    {
        $recipientEmail = trim((string) ($enrollment->user?->email ?? ''));
        if ($recipientEmail === '') {
            return false;
        }

        $subject = self::render($subjectTemplate, $enrollment);
        $body = self::render($bodyTemplate, $enrollment);

        try {
            Mail::raw($body, function ($message) use ($recipientEmail, $subject): void {
                $message->to($recipientEmail)->subject($subject);
            });
            return true;
        } catch (Throwable) {
            return false;
        }
    }
}

