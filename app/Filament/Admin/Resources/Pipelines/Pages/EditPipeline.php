<?php

namespace App\Filament\Admin\Resources\Pipelines\Pages;

use App\Filament\Admin\Resources\Pipelines\PipelineResource;
use App\Pipelines\AdapterRegistry;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\EditRecord;

class EditPipeline extends EditRecord
{
    protected static string $resource = PipelineResource::class;

    protected function getFormSchema(): array
    {
        /** @var AdapterRegistry $registry */
        $registry = app(AdapterRegistry::class);

        return [
            Section::make('Основное')
                ->schema([
                    TextInput::make('name')
                        ->label('Название')
                        ->required()
                        ->maxLength(255),

                    Select::make('type')
                        ->label('Тип')
                        ->options(\App\Models\Pipeline::TYPES)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set) {
                            $set('adapter', null);
                            $set('config', []);
                        }),

                    Select::make('adapter')
                        ->label('Адаптер')
                        ->required()
                        ->live()
                        ->options(function (Get $get) use ($registry): array {
                            $type = $get('type');
                            if (!$type) {
                                return [];
                            }
                            return $type === \App\Models\Pipeline::TYPE_EXPORT
                                ? $registry->listExports()
                                : $registry->listImports();
                        })
                        ->afterStateUpdated(function (Set $set) {
                            $set('config', []);
                        }),

                    Select::make('format')
                        ->label('Формат')
                        ->options(\App\Models\Pipeline::FORMATS)
                        ->default('csv')
                        ->required(),
                ]),

            Section::make('Расписание')
                ->schema([
                    TextInput::make('schedule')
                        ->label('Cron-выражение')
                        ->placeholder('0 * * * * (каждый час)')
                        ->helperText('Оставьте пустым для ручного запуска')
                        ->maxLength(100),

                    Toggle::make('is_active')
                        ->label('Активен')
                        ->default(true),
                ]),

            Section::make('Конфигурация адаптера')
                ->schema(function (Get $get) use ($registry): array {
                    return $this->buildConfigFields($get, $registry);
                })
                ->visible(fn (Get $get): bool => filled($get('adapter'))),
        ];
    }

    private function buildConfigFields(Get $get, AdapterRegistry $registry): array
    {
        $adapterCode = $get('adapter');
        $type = $get('type');

        if (!$adapterCode || !$type) {
            return [];
        }

        try {
            $adapter = $registry->getAdapter($adapterCode, $type);
        } catch (\Throwable) {
            return [];
        }

        $schema = $adapter->configSchema();
        $fields = [];

        foreach ($schema as $key => $def) {
            $fields[] = $this->makeField("config.{$key}", $key, $def);
        }

        return $fields;
    }

    private function makeField(string $name, string $key, array $def): Forms\Components\Component
    {
        $label = $def['label'] ?? ucfirst($key);
        $type = $def['type'] ?? 'text';
        $required = $def['required'] ?? false;
        $helperText = $def['help'] ?? null;

        return match ($type) {
            'select' => Select::make($name)
                ->label($label)
                ->options($def['options'] ?? [])
                ->default($def['default'] ?? null)
                ->required($required)
                ->helperText($helperText),

            'multiselect' => Forms\Components\CheckboxList::make($name)
                ->label($label)
                ->options($def['options'] ?? [])
                ->default($def['default'] ?? null)
                ->helperText($helperText),

            'number' => TextInput::make($name)
                ->label($label)
                ->numeric()
                ->default($def['default'] ?? null)
                ->required($required)
                ->helperText($helperText),

            'checkbox', 'toggle', 'boolean' => Toggle::make($name)
                ->label($label)
                ->default($def['default'] ?? false)
                ->helperText($helperText),

            default => TextInput::make($name)
                ->label($name)
                ->placeholder($def['placeholder'] ?? null)
                ->default($def['default'] ?? null)
                ->required($required)
                ->helperText($helperText),
        };
    }
}
