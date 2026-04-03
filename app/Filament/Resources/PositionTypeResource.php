<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PositionTypeResource\Pages;
use App\Models\positionType;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;

class PositionTypeResource extends Resource
{
    protected static ?string $model = positionType::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Organization';
    protected static ?int $navigationSort = 20;
    protected static ?string $navigationLabel = 'Designations';
    protected static ?string $modelLabel = 'Designation';
    protected static ?string $pluralModelLabel = 'Designations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('position')
                    ->label('Designation Title')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')
                    ->label('Designation')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->toggleable(),
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
            'index' => Pages\ListPositionTypes::route('/'),
            'create' => Pages\CreatePositionType::route('/create'),
            'edit' => Pages\EditPositionType::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManageOrganization();
    }
}
