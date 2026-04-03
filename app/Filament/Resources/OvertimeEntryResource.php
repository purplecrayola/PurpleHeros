<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OvertimeEntryResource\Pages;
use App\Models\OvertimeEntry;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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

class OvertimeEntryResource extends Resource
{
    protected static ?string $model = OvertimeEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Time & Attendance';
    protected static ?int $navigationSort = 50;
    protected static ?string $navigationLabel = 'Overtime';
    protected static ?string $modelLabel = 'Overtime Entry';
    protected static ?string $pluralModelLabel = 'Overtime Entries';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Employee')
                    ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'user_id')->all())
                    ->searchable()
                    ->required(),
                DatePicker::make('ot_date')
                    ->required()
                    ->native(false),
                TextInput::make('hours')
                    ->numeric()
                    ->required()
                    ->minValue(0.5)
                    ->maxValue(24)
                    ->step(0.5)
                    ->default(0.5),
                TextInput::make('ot_type')
                    ->label('Overtime Type')
                    ->required()
                    ->maxLength(255),
                Select::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                    ])
                    ->default('Pending')
                    ->required(),
                TextInput::make('approved_by')
                    ->maxLength(255)
                    ->disabled(),
                TextInput::make('approved_at')
                    ->label('Approved At')
                    ->disabled(),
                Textarea::make('rejection_reason')
                    ->maxLength(255)
                    ->rows(2),
                Textarea::make('description')
                    ->maxLength(1000)
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
                TextColumn::make('ot_date')
                    ->label('Date')
                    ->date('M j, Y')
                    ->sortable(),
                TextColumn::make('hours')
                    ->sortable(),
                TextColumn::make('ot_type')
                    ->label('Type')
                    ->searchable()
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
                    ->visible(fn (OvertimeEntry $record): bool => $record->status !== 'Approved')
                    ->action(function (OvertimeEntry $record): void {
                        $record->approveBy(auth()->user());
                    }),
                Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        TextInput::make('reason')->label('Reason')->maxLength(255),
                    ])
                    ->visible(fn (OvertimeEntry $record): bool => $record->status !== 'Rejected')
                    ->action(function (OvertimeEntry $record, array $data): void {
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
            'index' => Pages\ListOvertimeEntries::route('/'),
            'create' => Pages\CreateOvertimeEntry::route('/create'),
            'edit' => Pages\EditOvertimeEntry::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManageTimeAttendance();
    }
}
