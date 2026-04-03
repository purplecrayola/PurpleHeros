<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LearningCourseResource\Pages;
use App\Models\LearningCourse;
use App\Models\ProfileInformation;
use App\Models\User;
use Filament\Forms\Get;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LearningCourseResource extends Resource
{
    protected static ?string $model = LearningCourse::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Learning';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Course Catalog';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('course_code')->maxLength(60)->unique(ignoreRecord: true),
            TextInput::make('title')->required()->maxLength(255),
            TextInput::make('slug')->maxLength(255)->unique(ignoreRecord: true),
            Select::make('delivery_mode')
                ->options([
                    'physical' => 'Physical',
                    'virtual' => 'Virtual',
                    'self_paced' => 'Self-paced',
                    'other' => 'Other',
                ])->required()->default('virtual'),
            Select::make('visibility_status')
                ->options([
                    'draft' => 'Draft',
                    'published' => 'Published',
                    'archived' => 'Archived',
                ])->required()->default('published'),
            Select::make('status')
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ])->required()->default('active'),
            Toggle::make('catalog_visible')->default(true),
            Select::make('audience_scope')
                ->label('Audience Scope')
                ->options([
                    'all' => 'All Employees',
                    'filtered' => 'Selected Departments/Roles/Locations',
                ])
                ->default('all')
                ->required()
                ->reactive(),
            Select::make('target_departments')
                ->label('Target Departments')
                ->multiple()
                ->searchable()
                ->options(fn (): array => User::query()
                    ->whereNotNull('department')
                    ->where('department', '!=', '')
                    ->distinct()
                    ->orderBy('department')
                    ->pluck('department', 'department')
                    ->all())
                ->visible(fn (Get $get): bool => $get('audience_scope') === 'filtered'),
            Select::make('target_roles')
                ->label('Target Roles')
                ->multiple()
                ->searchable()
                ->options(fn (): array => User::query()
                    ->whereNotNull('role_name')
                    ->where('role_name', '!=', '')
                    ->distinct()
                    ->orderBy('role_name')
                    ->pluck('role_name', 'role_name')
                    ->all())
                ->visible(fn (Get $get): bool => $get('audience_scope') === 'filtered'),
            Select::make('target_locations')
                ->label('Target Locations (State/Country)')
                ->multiple()
                ->searchable()
                ->options(function (): array {
                    $states = ProfileInformation::query()
                        ->whereNotNull('state')
                        ->where('state', '!=', '')
                        ->distinct()
                        ->orderBy('state')
                        ->pluck('state')
                        ->all();
                    $countries = ProfileInformation::query()
                        ->whereNotNull('country')
                        ->where('country', '!=', '')
                        ->distinct()
                        ->orderBy('country')
                        ->pluck('country')
                        ->all();

                    $items = array_values(array_unique(array_merge($states, $countries)));
                    sort($items);

                    return array_combine($items, $items) ?: [];
                })
                ->visible(fn (Get $get): bool => $get('audience_scope') === 'filtered'),
            TextInput::make('join_link')->url()->maxLength(255),
            TextInput::make('venue')->maxLength(255),
            DateTimePicker::make('start_at')->seconds(false),
            DateTimePicker::make('end_at')->seconds(false),
            TextInput::make('estimated_duration_minutes')->numeric()->minValue(1),
            Textarea::make('description')->rows(4)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->limit(40),
                TextColumn::make('delivery_mode')->badge()->sortable(),
                TextColumn::make('visibility_status')->badge()->label('Catalog')->sortable(),
                TextColumn::make('catalog_visible')->label('Visible')->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No')
                    ->colors(['success' => true, 'gray' => false]),
                TextColumn::make('audience_scope')
                    ->label('Audience')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === 'filtered' ? 'Filtered' : 'All'),
                TextColumn::make('status')->badge(),
                TextColumn::make('assets_count')->counts('assets')->label('Assets'),
                TextColumn::make('enrollments_count')->counts('enrollments')->label('Enrollments'),
                TextColumn::make('updated_at')->dateTime('M j, Y H:i')->label('Updated')->sortable(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLearningCourses::route('/'),
            'create' => Pages\CreateLearningCourse::route('/create'),
            'edit' => Pages\EditLearningCourse::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManagePeopleOps();
    }
}
