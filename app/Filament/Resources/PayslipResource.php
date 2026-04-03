<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayslipResource\Pages;
use App\Models\Payslip;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayslipResource extends Resource
{
    protected static ?string $model = Payslip::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Payroll';
    protected static ?int $navigationSort = 8;
    protected static ?string $navigationLabel = 'Payslips';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Employee')
                    ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'user_id')->all())
                    ->searchable()
                    ->required(),
                TextInput::make('period_year')->required()->numeric()->minValue(2000)->maxValue(2100),
                Select::make('period_month')->required()->options([
                    1 => 'January',
                    2 => 'February',
                    3 => 'March',
                    4 => 'April',
                    5 => 'May',
                    6 => 'June',
                    7 => 'July',
                    8 => 'August',
                    9 => 'September',
                    10 => 'October',
                    11 => 'November',
                    12 => 'December',
                ]),
                Select::make('source')
                    ->options([
                        'generated' => 'Generated',
                        'imported' => 'Imported',
                        'uploaded' => 'Uploaded',
                    ])
                    ->default('uploaded')
                    ->required(),
                FileUpload::make('file_path')
                    ->label('Payslip File (PDF)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('public')
                    ->directory('payslips-uploaded')
                    ->preserveFilenames(),
                Textarea::make('payload')
                    ->label('JSON Payload (Optional)')
                    ->rows(6)
                    ->formatStateUsing(fn ($state): string => json_encode($state ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                    ->dehydrateStateUsing(function ($state): array {
                        if (! is_string($state) || trim($state) === '') {
                            return [];
                        }

                        $decoded = json_decode($state, true);

                        return is_array($decoded) ? $decoded : [];
                    }),
                Select::make('is_locked')
                    ->label('Locked')
                    ->options([0 => 'No', 1 => 'Yes'])
                    ->default(1)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')->label('Employee ID')->searchable()->sortable(),
                TextColumn::make('period_year')->sortable(),
                TextColumn::make('period_month')
                    ->formatStateUsing(fn ($state): string => date('M', mktime(0, 0, 0, (int) $state, 1)))
                    ->sortable(),
                BadgeColumn::make('source')->colors([
                    'success' => 'generated',
                    'warning' => 'imported',
                    'info' => 'uploaded',
                ]),
                IconColumn::make('is_locked')->boolean(),
                TextColumn::make('issued_at')->dateTime('M j, Y H:i')->placeholder('-')->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayslips::route('/'),
            'create' => Pages\CreatePayslip::route('/create'),
            'edit' => Pages\EditPayslip::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManagePayroll();
    }
}
