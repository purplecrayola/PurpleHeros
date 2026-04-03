<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LearningEnrollmentResource\Pages;
use App\Models\LearningCourse;
use App\Models\LearningEnrollment;
use App\Models\LearningProgressEvent;
use App\Models\User;
use App\Support\LearningNotificationManager;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class LearningEnrollmentResource extends Resource
{
    protected static ?string $model = LearningEnrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Learning';
    protected static ?int $navigationSort = 12;
    protected static ?string $navigationLabel = 'Enrollments';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('learning_course_id')
                ->label('Course')
                ->options(fn (): array => LearningCourse::query()->orderBy('title')->pluck('title', 'id')->all())
                ->searchable()
                ->preload()
                ->required(),
            Select::make('user_id')
                ->label('Employee')
                ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'user_id')->all())
                ->searchable()
                ->preload()
                ->required(),
            Select::make('status')
                ->options([
                    'not_started' => 'Not Started',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'overdue' => 'Overdue',
                ])->required()->default('not_started'),
            TextInput::make('completion_percent')->numeric()->minValue(0)->maxValue(100)->default(0),
            Toggle::make('is_mandatory')->default(false),
            DateTimePicker::make('assigned_at')->seconds(false),
            DateTimePicker::make('due_at')->seconds(false),
            DateTimePicker::make('started_at')->seconds(false),
            DateTimePicker::make('last_activity_at')->seconds(false),
            DateTimePicker::make('completed_at')->seconds(false),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.title')->label('Course')->searchable()->limit(30),
                TextColumn::make('user.name')->label('Employee')->searchable()->limit(24),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('completion_percent')->label('Completion %')->sortable(),
                TextColumn::make('is_mandatory')->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Mandatory' : 'Optional')
                    ->colors(['warning' => true, 'gray' => false]),
                TextColumn::make('due_at')->dateTime('M j, Y')->sortable()->toggleable(),
                TextColumn::make('last_activity_at')->dateTime('M j, Y H:i')->label('Last Activity')->sortable()->toggleable(),
                TextColumn::make('updated_at')->dateTime('M j, Y H:i')->label('Updated')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'not_started' => 'Not Started',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'overdue' => 'Overdue',
                    ]),
                Filter::make('mandatory_only')
                    ->label('Mandatory Only')
                    ->query(fn (Builder $query): Builder => $query->where('is_mandatory', true)),
                Filter::make('overdue_7_days')
                    ->label('Overdue > 7 Days')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereNotNull('due_at')
                        ->where('due_at', '<', Carbon::now()->subDays(7))
                        ->where('status', '!=', 'completed')),
                Filter::make('zero_activity')
                    ->label('Zero Activity')
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave('progressEvents')),
            ])
            ->headerActions([
                Action::make('remindOverdueByCourse')
                    ->label('Remind Overdue By Course')
                    ->icon('heroicon-o-bell-alert')
                    ->color('warning')
                    ->form([
                        Select::make('learning_course_id')
                            ->label('Course')
                            ->options(fn (): array => LearningCourse::query()->orderBy('title')->pluck('title', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->required(),
                        Textarea::make('message')
                            ->label('Reminder Message')
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder('Friendly reminder to complete your overdue learning assignment.'),
                    ])
                    ->action(function (array $data): void {
                        $courseId = (int) $data['learning_course_id'];
                        $message = trim((string) ($data['message'] ?? ''));

                        $overdue = LearningEnrollment::query()
                            ->with(['course', 'user'])
                            ->where('learning_course_id', $courseId)
                            ->whereNotNull('due_at')
                            ->where('due_at', '<', Carbon::now())
                            ->where('status', '!=', 'completed')
                            ->get();

                        if ($overdue->isEmpty()) {
                            Notification::make()
                                ->title('No overdue enrollments found for the selected course.')
                                ->warning()
                                ->send();
                            return;
                        }

                        $loggedCount = 0;
                        $mailCount = 0;

                        foreach ($overdue as $record) {
                            LearningProgressEvent::query()->create([
                                'learning_enrollment_id' => $record->id,
                                'event_type' => 'manager_bulk_reminder',
                                'progress_percent' => $record->completion_percent,
                                'created_by_user_id' => auth()->user()?->user_id,
                                'meta' => [
                                    'message' => $message !== '' ? $message : 'Bulk reminder sent by manager.',
                                    'trigger' => 'bulk_course_intervention',
                                ],
                            ]);
                            $loggedCount++;

                            if (LearningNotificationManager::sendReminderNotification($record, $message !== '' ? $message : null)) {
                                $mailCount++;
                            }
                        }

                        Notification::make()
                            ->title("Bulk reminder completed: {$loggedCount} logged, {$mailCount} emailed.")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Action::make('sendReminder')
                    ->label('Send Reminder')
                    ->icon('heroicon-o-bell-alert')
                    ->color('warning')
                    ->visible(fn (LearningEnrollment $record): bool => $record->status !== 'completed')
                    ->form([
                        Textarea::make('message')
                            ->label('Reminder Message')
                            ->rows(3)
                            ->placeholder('Friendly reminder to complete this course.')
                            ->maxLength(1000),
                    ])
                    ->action(function (LearningEnrollment $record, array $data): void {
                        $message = trim((string) ($data['message'] ?? ''));
                        $mailSent = false;

                        LearningProgressEvent::query()->create([
                            'learning_enrollment_id' => $record->id,
                            'event_type' => 'manager_reminder',
                            'progress_percent' => $record->completion_percent,
                            'created_by_user_id' => auth()->user()?->user_id,
                            'meta' => [
                                'message' => $message !== '' ? $message : 'Reminder sent by manager.',
                                'trigger' => 'manual_intervention',
                            ],
                        ]);

                        $mailSent = LearningNotificationManager::sendReminderNotification($record, $message !== '' ? $message : null);

                        $record->last_activity_at = now();
                        $record->save();

                        $notification = Notification::make()
                            ->title($mailSent ? 'Reminder sent and logged.' : 'Reminder logged (email not sent).');

                        if ($mailSent) {
                            $notification->success();
                        } else {
                            $notification->warning();
                        }

                        $notification->send();
                    }),
                Action::make('extendDueDate')
                    ->label('Extend Due Date')
                    ->icon('heroicon-o-calendar-days')
                    ->color('info')
                    ->visible(fn (LearningEnrollment $record): bool => $record->status !== 'completed')
                    ->form([
                        DateTimePicker::make('due_at')
                            ->label('New Due Date')
                            ->seconds(false)
                            ->required(),
                        Textarea::make('note')
                            ->label('Reason / Note')
                            ->rows(2)
                            ->maxLength(1000),
                    ])
                    ->action(function (LearningEnrollment $record, array $data): void {
                        $previousDueAt = $record->due_at;
                        $record->due_at = $data['due_at'];
                        if ($record->status === 'overdue') {
                            $record->status = 'in_progress';
                        }
                        $record->last_activity_at = now();
                        $record->save();

                        LearningProgressEvent::query()->create([
                            'learning_enrollment_id' => $record->id,
                            'event_type' => 'due_date_extended',
                            'progress_percent' => $record->completion_percent,
                            'created_by_user_id' => auth()->user()?->user_id,
                            'meta' => [
                                'previous_due_at' => optional($previousDueAt)?->toDateTimeString(),
                                'new_due_at' => optional($record->due_at)?->toDateTimeString(),
                                'note' => trim((string) ($data['note'] ?? '')),
                            ],
                        ]);

                        Notification::make()
                            ->title('Due date updated.')
                            ->success()
                            ->send();
                    }),
                Action::make('markComplete')
                    ->label('Mark Complete')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (LearningEnrollment $record): bool => $record->status !== 'completed')
                    ->form([
                        Textarea::make('note')
                            ->label('Completion Note')
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder('Completed after manager review / verification.'),
                    ])
                    ->requiresConfirmation()
                    ->action(function (LearningEnrollment $record, array $data): void {
                        $record->status = 'completed';
                        $record->completion_percent = 100;
                        $record->completed_at = now();
                        $record->last_activity_at = now();
                        $record->save();

                        LearningProgressEvent::query()->create([
                            'learning_enrollment_id' => $record->id,
                            'event_type' => 'manager_completed',
                            'progress_percent' => 100,
                            'created_by_user_id' => auth()->user()?->user_id,
                            'meta' => [
                                'note' => trim((string) ($data['note'] ?? '')),
                                'trigger' => 'manual_intervention',
                            ],
                        ]);

                        LearningNotificationManager::sendCompletionNotification(
                            $record,
                            trim((string) ($data['note'] ?? '')) !== '' ? (string) $data['note'] : null
                        );

                        Notification::make()
                            ->title('Enrollment marked as completed.')
                            ->success()
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('sendReminderToSelected')
                        ->label('Send Reminder (Selected)')
                        ->icon('heroicon-o-bell-alert')
                        ->color('warning')
                        ->form([
                            Textarea::make('message')
                                ->label('Reminder Message')
                                ->rows(3)
                                ->maxLength(1000),
                        ])
                        ->action(function ($records, array $data): void {
                            $message = trim((string) ($data['message'] ?? ''));
                            $loggedCount = 0;
                            $mailCount = 0;

                            foreach ($records as $record) {
                                if ($record->status === 'completed') {
                                    continue;
                                }

                                LearningProgressEvent::query()->create([
                                    'learning_enrollment_id' => $record->id,
                                    'event_type' => 'manager_bulk_selected_reminder',
                                    'progress_percent' => $record->completion_percent,
                                    'created_by_user_id' => auth()->user()?->user_id,
                                    'meta' => [
                                        'message' => $message !== '' ? $message : 'Bulk selected reminder sent by manager.',
                                        'trigger' => 'bulk_selected_intervention',
                                    ],
                                ]);
                                $loggedCount++;

                                if (LearningNotificationManager::sendReminderNotification($record, $message !== '' ? $message : null)) {
                                    $mailCount++;
                                }
                            }

                            Notification::make()
                                ->title("Selected reminders done: {$loggedCount} logged, {$mailCount} emailed.")
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLearningEnrollments::route('/'),
            'create' => Pages\CreateLearningEnrollment::route('/create'),
            'edit' => Pages\EditLearningEnrollment::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManagePeopleOps();
    }
}
