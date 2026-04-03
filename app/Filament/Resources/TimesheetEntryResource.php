<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimesheetEntryResource\Pages;
use App\Models\TimesheetEntry;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Get;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TimesheetEntryResource extends Resource
{
    protected static ?string $model = TimesheetEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Time & Attendance';
    protected static ?int $navigationSort = 40;
    protected static ?string $navigationLabel = 'Timesheets';
    protected static ?string $modelLabel = 'Timesheet Entry';
    protected static ?string $pluralModelLabel = 'Timesheet Entries';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Employee')
                    ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'user_id')->all())
                    ->searchable()
                    ->required(),
                DatePicker::make('work_date')
                    ->required()
                    ->native(false),
                TextInput::make('project_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('assigned_hours')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(24)
                    ->default(0),
                TextInput::make('worked_hours')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(24)
                    ->rule(function (Get $get) {
                        return function (string $attribute, $value, \Closure $fail) use ($get): void {
                            $assigned = (int) ($get('assigned_hours') ?? 0);
                            $worked = (int) $value;
                            $maxAllowed = min($assigned + 4, 24);

                            if ($worked > $maxAllowed) {
                                $fail("Worked hours cannot exceed assigned hours by more than 4 (max {$maxAllowed}).");
                            }
                        };
                    })
                    ->helperText('Policy: worked hours can exceed assigned hours by at most 4.')
                    ->default(0),
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
                TextColumn::make('work_date')
                    ->label('Date')
                    ->date('M j, Y')
                    ->sortable(),
                TextColumn::make('project_name')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assigned_hours')
                    ->label('Assigned')
                    ->sortable(),
                TextColumn::make('worked_hours')
                    ->label('Worked')
                    ->sortable(),
            ])
            ->filters([
            ])
            ->actions([
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
            'index' => Pages\ListTimesheetEntries::route('/'),
            'create' => Pages\CreateTimesheetEntry::route('/create'),
            'edit' => Pages\EditTimesheetEntry::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManageTimeAttendance();
    }
}
