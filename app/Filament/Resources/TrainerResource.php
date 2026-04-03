<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainerResource\Pages;
use App\Models\Trainer;
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

class TrainerResource extends Resource
{
    protected static ?string $model = Trainer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Learning';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Trainers';
    protected static ?string $modelLabel = 'Trainer';
    protected static ?string $pluralModelLabel = 'Trainers';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('full_name')
                    ->label('Full Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('trainer_id')
                    ->label('Trainer Code')
                    ->maxLength(255),
                TextInput::make('role')
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->maxLength(255),
                Select::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                    ])
                    ->default('Active')
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
                TextColumn::make('full_name')->label('Name')->searchable()->sortable(),
                TextColumn::make('trainer_id')->label('Trainer Code')->searchable()->toggleable(),
                TextColumn::make('role')->searchable()->toggleable(),
                TextColumn::make('email')->searchable()->toggleable(),
                TextColumn::make('phone')->searchable()->toggleable(),
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
            'index' => Pages\ListTrainers::route('/'),
            'create' => Pages\CreateTrainer::route('/create'),
            'edit' => Pages\EditTrainer::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManagePeopleOps();
    }
}
