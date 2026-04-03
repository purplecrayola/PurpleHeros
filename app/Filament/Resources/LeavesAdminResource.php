<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeavesAdminResource\Pages;
use App\Models\LeavePolicyBand;
use App\Models\LeavesAdmin;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Get;
use Filament\Forms\Components\Select;
use Filament\Forms\Set;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeavesAdminResource extends Resource
{
    protected static ?string $model = LeavesAdmin::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationGroup = 'Time & Attendance';
    protected static ?int $navigationSort = 20;
    protected static ?string $navigationLabel = 'Leave Requests';
    protected static ?string $modelLabel = 'Leave Request';
    protected static ?string $pluralModelLabel = 'Leave Requests';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Employee')
                    ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'user_id')->all())
                    ->searchable()
                    ->required(),
                Select::make('leave_type')
                    ->label('Leave Type')
                    ->options(function (?LeavesAdmin $record): array {
                        $options = LeavePolicyBand::activeOptions();
                        $current = trim((string) ($record?->leave_type ?? ''));
                        if ($current !== '' && ! array_key_exists($current, $options)) {
                            $options = [$current => $current . ' (legacy)'] + $options;
                        }

                        return $options;
                    })
                    ->searchable()
                    ->required(),
                DatePicker::make('from_date')
                    ->required()
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->format('Y-m-d')
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set): void {
                        $set('day', self::calculateLeaveDays($get('from_date'), $get('to_date')));
                    }),
                DatePicker::make('to_date')
                    ->required()
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->format('Y-m-d')
                    ->live()
                    ->afterOrEqual('from_date')
                    ->afterStateUpdated(function (Get $get, Set $set): void {
                        $set('day', self::calculateLeaveDays($get('from_date'), $get('to_date')));
                    }),
                TextInput::make('day')
                    ->label('Total Days')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->readOnly(),
                Select::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                    ])
                    ->default('Pending')
                    ->required(),
                TextInput::make('approved_by')
                    ->label('Approved By')
                    ->maxLength(255)
                    ->disabled(),
                TextInput::make('approved_at')
                    ->label('Approved At')
                    ->disabled(),
                Textarea::make('rejection_reason')
                    ->maxLength(255)
                    ->rows(2),
                Textarea::make('leave_reason')
                    ->required()
                    ->maxLength(255)
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
                TextColumn::make('leave_type')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('from_date')
                    ->label('From')
                    ->sortable(),
                TextColumn::make('to_date')
                    ->label('To')
                    ->sortable(),
                TextColumn::make('day')
                    ->label('Days')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('approved_by')
                    ->label('Approved By')
                    ->toggleable(),
                TextColumn::make('approved_at')
                    ->dateTime('M j, Y H:i')
                    ->label('Approved At')
                    ->toggleable(),
            ])
            ->filters([
            ])
            ->actions([
                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (LeavesAdmin $record): bool => $record->status !== 'Approved')
                    ->action(function (LeavesAdmin $record): void {
                        $record->approveBy(auth()->user());
                    }),
                Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        TextInput::make('reason')->label('Reason')->maxLength(255),
                    ])
                    ->visible(fn (LeavesAdmin $record): bool => $record->status !== 'Rejected')
                    ->action(function (LeavesAdmin $record, array $data): void {
                        $record->rejectBy(auth()->user(), $data['reason'] ?? null);
                    }),
                ViewAction::make(),
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
            'index' => Pages\ListLeavesAdmins::route('/'),
            'create' => Pages\CreateLeavesAdmin::route('/create'),
            'edit' => Pages\EditLeavesAdmin::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManageTimeAttendance();
    }

    private static function calculateLeaveDays($fromDate, $toDate): int
    {
        if (! $fromDate || ! $toDate) {
            return 1;
        }

        try {
            $from = Carbon::parse($fromDate)->startOfDay();
            $to = Carbon::parse($toDate)->startOfDay();

            if ($to->lt($from)) {
                return 1;
            }

            return (int) $from->diffInDays($to) + 1;
        } catch (\Throwable $exception) {
            return 1;
        }
    }
}
