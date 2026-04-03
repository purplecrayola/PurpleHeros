<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LearningAssetResource\Pages;
use App\Models\LearningAsset;
use App\Models\LearningCourse;
use Filament\Forms\Components\FileUpload;
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

class LearningAssetResource extends Resource
{
    protected static ?string $model = LearningAsset::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Learning';
    protected static ?int $navigationSort = 11;
    protected static ?string $navigationLabel = 'Course Assets';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('learning_course_id')
                ->label('Course')
                ->options(fn (): array => LearningCourse::query()->orderBy('title')->pluck('title', 'id')->all())
                ->searchable()
                ->preload()
                ->required(),
            Select::make('asset_type')
                ->options([
                    'pdf' => 'PDF',
                    'audio' => 'Audio',
                    'video' => 'Video',
                    'link' => 'External Link',
                    'file' => 'Download File',
                ])->required()->default('pdf'),
            TextInput::make('title')->required()->maxLength(255),
            Textarea::make('description')->rows(3),
            FileUpload::make('file_path')
                ->label('Asset File')
                ->disk('public')
                ->directory('learning-assets')
                ->visibility('public'),
            TextInput::make('external_url')->url()->maxLength(255),
            TextInput::make('sort_order')->numeric()->minValue(1)->default(1),
            Toggle::make('is_required')->default(true),
            TextInput::make('duration_seconds')->numeric()->minValue(1),
            TextInput::make('pages_count')->numeric()->minValue(1),
            Select::make('status')
                ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                ->default('active')
                ->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.title')->label('Course')->searchable()->limit(30),
                TextColumn::make('title')->searchable()->limit(30),
                TextColumn::make('asset_type')->badge(),
                TextColumn::make('sort_order')->sortable(),
                TextColumn::make('is_required')->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Required' : 'Optional')
                    ->colors(['success' => true, 'gray' => false]),
                TextColumn::make('status')->badge(),
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
            'index' => Pages\ListLearningAssets::route('/'),
            'create' => Pages\CreateLearningAsset::route('/create'),
            'edit' => Pages\EditLearningAsset::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManagePeopleOps();
    }
}

