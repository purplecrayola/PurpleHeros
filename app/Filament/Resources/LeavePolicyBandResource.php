<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeavePolicyBandResource\Pages;
use App\Models\LeavePolicyBand;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeavePolicyBandResource extends Resource
{
    protected static ?string $model = LeavePolicyBand::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Time & Attendance';
    protected static ?int $navigationSort = 18;
    protected static ?string $navigationLabel = 'Leave Policy Bands';
    protected static ?string $modelLabel = 'Leave Policy Band';
    protected static ?string $pluralModelLabel = 'Leave Policy Bands';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            Select::make('category')
                ->required()
                ->options([
                    'annual' => 'Annual',
                    'sick' => 'Sick',
                    'maternity' => 'Maternity',
                    'other' => 'Other',
                    'unpaid' => 'Unpaid',
                ])
                ->default('other'),
            TextInput::make('annual_entitlement_days')
                ->label('Annual Entitlement (Days)')
                ->numeric()
                ->minValue(0)
                ->nullable()
                ->helperText('Leave blank for uncapped leave types such as unpaid leave.'),
            Toggle::make('carry_forward_enabled')
                ->label('Carry Forward Enabled')
                ->inline(false)
                ->default(false)
                ->live(),
            TextInput::make('carry_forward_cap_days')
                ->label('Carry Forward Cap (Days)')
                ->numeric()
                ->minValue(0)
                ->nullable()
                ->visible(fn ($get): bool => (bool) $get('carry_forward_enabled')),
            TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->minValue(0)
                ->required(),
            Toggle::make('is_active')
                ->label('Active')
                ->default(true),
            Textarea::make('description')
                ->rows(3)
                ->maxLength(1000)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category')->badge()->sortable(),
                TextColumn::make('annual_entitlement_days')
                    ->label('Entitlement')
                    ->formatStateUsing(fn ($state): string => $state === null ? 'Uncapped' : ((int) $state . ' days'))
                    ->sortable(),
                TextColumn::make('carry_forward_cap_days')
                    ->label('Carry Forward Cap')
                    ->formatStateUsing(fn ($state): string => $state === null ? '-' : ((int) $state . ' days')),
                IconColumn::make('is_active')->boolean()->label('Active'),
                TextColumn::make('sort_order')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeavePolicyBands::route('/'),
            'create' => Pages\CreateLeavePolicyBand::route('/create'),
            'edit' => Pages\EditLeavePolicyBand::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManageTimeAttendance();
    }
}

