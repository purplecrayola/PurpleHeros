<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainingResource\Pages;
use App\Models\Training;
use App\Models\TrainingType;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
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

class TrainingResource extends Resource
{
    protected static ?string $model = Training::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Learning';
    protected static ?int $navigationSort = 30;
    protected static ?string $navigationLabel = 'Training Sessions';
    protected static ?string $modelLabel = 'Training Session';
    protected static ?string $pluralModelLabel = 'Training Sessions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('trainer_id')
                    ->label('Trainer')
                    ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'user_id')->all())
                    ->searchable()
                    ->preload(),
                TextInput::make('trainer')
                    ->label('Trainer Name')
                    ->maxLength(255),
                TextInput::make('employees_id')
                    ->label('Employee ID')
                    ->maxLength(255),
                TextInput::make('employees')
                    ->label('Employee Name')
                    ->maxLength(255),
                Select::make('training_type')
                    ->label('Training Type')
                    ->options(fn (): array => TrainingType::query()->where('status', 'Active')->orderBy('type')->pluck('type', 'type')->all())
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('training_cost')
                    ->label('Training Cost (NGN)')
                    ->numeric()
                    ->prefix('NGN')
                    ->default(0),
                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->native(false),
                DatePicker::make('end_date')
                    ->label('End Date')
                    ->native(false),
                Select::make('status')
                    ->options([
                        'Planned' => 'Planned',
                        'In Progress' => 'In Progress',
                        'Completed' => 'Completed',
                        'Cancelled' => 'Cancelled',
                    ])
                    ->default('Planned')
                    ->required(),
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('training_type')->label('Type')->searchable()->sortable(),
                TextColumn::make('trainer')->label('Trainer')->searchable()->toggleable(),
                TextColumn::make('employees')->label('Employee')->searchable()->toggleable(),
                TextColumn::make('training_cost')->label('Cost')->money('NGN')->sortable()->toggleable(),
                TextColumn::make('start_date')->label('Start')->toggleable(),
                TextColumn::make('end_date')->label('End')->toggleable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('updated_at')->dateTime('M j, Y H:i')->label('Last Updated')->sortable()->toggleable(),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrainings::route('/'),
            'create' => Pages\CreateTraining::route('/create'),
            'edit' => Pages\EditTraining::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManagePeopleOps();
    }
}
