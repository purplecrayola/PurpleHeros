<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayslipImportBatchResource\Pages;
use App\Models\PayrollRun;
use App\Models\PayslipImportBatch;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayslipImportBatchResource extends Resource
{
    protected static ?string $model = PayslipImportBatch::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationGroup = 'Payroll';
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationLabel = 'Payslip Imports';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')->required()->maxLength(255),
                Select::make('payroll_run_id')
                    ->label('Payroll Run (Optional)')
                    ->options(fn (): array => PayrollRun::query()->orderByDesc('period_year')->orderByDesc('period_month')->pluck('run_code', 'id')->all())
                    ->searchable(),
                TextInput::make('period_year')->numeric()->minValue(2000)->maxValue(2100),
                Select::make('period_month')->options([
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
                FileUpload::make('import_file_path')
                    ->label('Import File (CSV/XLSX)')
                    ->acceptedFileTypes([
                        'text/csv',
                        'application/csv',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ])
                    ->disk('local')
                    ->directory('payslip-imports')
                    ->preserveFilenames()
                    ->required(),
                TextInput::make('source_file_name')->maxLength(255),
                Select::make('status')->options([
                    'draft' => 'Draft',
                    'processed' => 'Processed',
                    'failed' => 'Failed',
                ])->default('draft')->required(),
                Textarea::make('notes')->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('period_year')->sortable()->placeholder('-'),
                TextColumn::make('period_month')
                    ->formatStateUsing(fn ($state): string => $state ? date('M', mktime(0, 0, 0, (int) $state, 1)) : '-')
                    ->sortable(),
                TextColumn::make('source_file_name')->label('File')->placeholder('-')->toggleable(),
                BadgeColumn::make('status')->colors([
                    'gray' => 'draft',
                    'success' => 'processed',
                    'danger' => 'failed',
                ]),
                TextColumn::make('processed_rows')->sortable(),
                TextColumn::make('failed_rows')->sortable(),
                TextColumn::make('updated_at')->dateTime('M j, Y H:i')->sortable()->toggleable(),
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
            'index' => Pages\ListPayslipImportBatches::route('/'),
            'create' => Pages\CreatePayslipImportBatch::route('/create'),
            'edit' => Pages\EditPayslipImportBatch::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManagePayroll();
    }
}
