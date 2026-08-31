<?php

namespace App\Filament\Admin\Resources\Pipelines;

use App\Filament\Admin\Resources\Pipelines\Pages\CreatePipeline;
use App\Filament\Admin\Resources\Pipelines\Pages\EditPipeline;
use App\Filament\Admin\Resources\Pipelines\Pages\ListPipelines;
use App\Filament\Admin\Resources\Pipelines\Pages\ViewPipeline;
use App\Filament\Admin\Resources\Pipelines\RelationManagers\LogsRelationManager;
use App\Filament\Admin\Resources\Pipelines\Tables\PipelinesTable;
use App\Models\Pipeline;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;

class PipelineResource extends Resource
{
    protected static ?string $model = Pipeline::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $navigationLabel = null; // use getNavigationLabel()
    protected static ?string $modelLabel = null; // use getModelLabel()
    protected static ?string $pluralModelLabel = null; // use getPluralModelLabel()

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return PipelinesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPipelines::route('/'),
            'create' => CreatePipeline::route('/create'),
            'view' => ViewPipeline::route('/{record}'),
            'edit' => EditPipeline::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            LogsRelationManager::class,
        ];
    }
}