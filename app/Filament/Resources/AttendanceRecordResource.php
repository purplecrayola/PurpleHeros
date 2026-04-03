<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceRecordResource\Pages;
use App\Models\AttendanceRecord;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceRecordResource extends Resource
{
    protected static ?string $model = AttendanceRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Time & Attendance';
    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Employee')
                    ->options(fn (): array => User::query()->pluck('name', 'user_id')->all())
                    ->searchable()
                    ->required(),
                DatePicker::make('attendance_date')
                    ->required()
                    ->native(false),
                Select::make('status')
                    ->options([
                        'Present' => 'Present',
                        'Absent' => 'Absent',
                        'Late' => 'Late',
                        'Remote' => 'Remote',
                    ])
                    ->required(),
                TimePicker::make('check_in')->seconds(false),
                TimePicker::make('check_out')->seconds(false),
                TextInput::make('work_minutes')->numeric()->default(0)->required(),
                TextInput::make('break_minutes')->numeric()->default(0)->required(),
                TextInput::make('overtime_minutes')->numeric()->default(0)->required(),
                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')
                    ->label('Employee ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('attendance_date')
                    ->label('Date')
                    ->date('M j, Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('check_in')
                    ->time('H:i')
                    ->toggleable(),
                TextColumn::make('check_out')
                    ->time('H:i')
                    ->toggleable(),
                TextColumn::make('work_minutes')
                    ->label('Work (min)')
                    ->sortable(),
                TextColumn::make('overtime_minutes')
                    ->label('OT (min)')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Present' => 'Present',
                        'Absent' => 'Absent',
                        'Late' => 'Late',
                        'Remote' => 'Remote',
                    ]),
                SelectFilter::make('user_id')
                    ->label('Employee')
                    ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'user_id')->all())
                    ->searchable(),
                Filter::make('attendance_date')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('attendance_date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('attendance_date', '<=', $date));
                    }),
                Filter::make('has_overtime')
                    ->label('Has overtime')
                    ->query(fn (Builder $query): Builder => $query->where('overtime_minutes', '>', 0)),
            ])
            ->headerActions([
                Action::make('exportMonthlySummary')
                    ->label('Export Monthly Summary')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $start = now()->startOfMonth()->toDateString();
                        $end = now()->endOfMonth()->toDateString();

                        $records = AttendanceRecord::query()
                            ->whereBetween('attendance_date', [$start, $end])
                            ->get();

                        $statusCounts = $records
                            ->groupBy('status')
                            ->map(fn ($group) => $group->count());

                        $filename = sprintf('attendance-summary-%s.csv', now()->format('Y-m'));

                        return response()->streamDownload(function () use ($records, $statusCounts, $start, $end): void {
                            $stream = fopen('php://output', 'w');
                            if ($stream === false) {
                                return;
                            }

                            fputcsv($stream, ['Attendance Summary']);
                            fputcsv($stream, ['Period Start', $start]);
                            fputcsv($stream, ['Period End', $end]);
                            fputcsv($stream, []);
                            fputcsv($stream, ['Status', 'Count']);

                            foreach (['Present', 'Remote', 'Late', 'Absent'] as $status) {
                                fputcsv($stream, [$status, (int) ($statusCounts[$status] ?? 0)]);
                            }

                            fputcsv($stream, []);
                            fputcsv($stream, ['Metric', 'Value']);
                            fputcsv($stream, ['Records', $records->count()]);
                            fputcsv($stream, ['Work Minutes', (int) $records->sum('work_minutes')]);
                            fputcsv($stream, ['Break Minutes', (int) $records->sum('break_minutes')]);
                            fputcsv($stream, ['Overtime Minutes', (int) $records->sum('overtime_minutes')]);

                            fclose($stream);
                        }, $filename, [
                            'Content-Type' => 'text/csv',
                        ]);
                    }),
            ])
            ->actions([
                ViewAction::make(),
                \Filament\Tables\Actions\Action::make('markPresent')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (AttendanceRecord $record): bool => $record->status !== 'Present')
                    ->action(fn (AttendanceRecord $record): bool => $record->update(['status' => 'Present'])),
                \Filament\Tables\Actions\Action::make('markAbsent')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (AttendanceRecord $record): bool => $record->status !== 'Absent')
                    ->action(fn (AttendanceRecord $record): bool => $record->update(['status' => 'Absent'])),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendanceRecords::route('/'),
            'create' => Pages\CreateAttendanceRecord::route('/create'),
            'edit' => Pages\EditAttendanceRecord::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManageTimeAttendance();
    }
}
