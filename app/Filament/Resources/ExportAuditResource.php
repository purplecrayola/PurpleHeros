<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExportAuditResource\Pages;
use App\Models\ExportAudit;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExportAuditResource extends Resource
{
    protected static ?string $model = ExportAudit::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 20;
    protected static ?string $navigationLabel = 'Export Audits';
    protected static ?string $modelLabel = 'Export Audit';
    protected static ?string $pluralModelLabel = 'Export Audits';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('exported_at', 'desc')
            ->columns([
                TextColumn::make('exported_at')
                    ->dateTime('M j, Y H:i:s')
                    ->label('Exported At')
                    ->sortable(),
                TextColumn::make('user_email')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('report_name')
                    ->label('Report')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('format')
                    ->badge()
                    ->sortable(),
                TextColumn::make('filename')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('report_date')
                    ->label('Report Date')
                    ->toggleable(),
                TextColumn::make('employee_search')
                    ->label('Employee Filter')
                    ->toggleable(),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('format')
                    ->options([
                        'csv' => 'CSV',
                        'xlsx' => 'XLSX',
                        'pdf' => 'PDF',
                    ]),
                SelectFilter::make('report_name')
                    ->options([
                        'Employee Report' => 'Employee Report',
                        'Leave Report' => 'Leave Report',
                        'Daily Attendance Report' => 'Daily Attendance Report',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
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
            'index' => Pages\ListExportAudits::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canViewReports();
    }
}
