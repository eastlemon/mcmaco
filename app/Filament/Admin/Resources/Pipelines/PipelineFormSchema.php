<?php

namespace App\Filament\Admin\Resources\Pipelines;

use App\Models\Pipeline;
use App\Pipelines\AdapterRegistry;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

trait PipelineFormSchema
{
    protected function pipelineFormSchema(): array
    {
        return [
            Section::make('Основное')
                ->schema([
                    TextInput::make('name')
                        ->label('Название')
                        ->required()
                        ->maxLength(255),

                    Select::make('type')
                        ->label('Тип')
                        ->options(Pipeline::TYPES)
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
                        ->options(function (Get $get): array {
                            $type = $get('type');
                            if (!$type) {
                                return [];
                            }
                            /** @var AdapterRegistry $registry */
                            $registry = app(AdapterRegistry::class);

                            return $type === Pipeline::TYPE_EXPORT
                                ? $registry->listExports()
                                : $registry->listImports();
                        })
                        ->afterStateUpdated(function (Set $set) {
                            $set('config', []);
                        }),

                    Select::make('format')
                        ->label('Формат')
                        ->options(Pipeline::FORMATS)
                        ->default('csv')
                        ->required(),
                ]),

            Section::make('Расписание')
                ->schema([
                    TextInput::make('schedule')
                        ->label('Cron-выражение')
                        ->placeholder('0 * * * * (каждый час)')
                        ->helperText('Например: 0 */6 * * * — каждые 6 часов. Оставьте пустым для ручного запуска.')
                        ->maxLength(100)
                        ->regex('/^[\d\/\*\,\-\s]+$/')
                        ->validationMessages([
                            'regex' => 'Только цифры, пробелы и символы / * , -',
                        ]),

                    Toggle::make('is_active')
                        ->label('Активен')
                        ->default(true),
                ]),

            Section::make('Конфигурация адаптера')
                ->schema(fn (Get $get): array => $this->buildConfigFields($get))
                ->visible(fn (Get $get): bool => filled($get('adapter'))),
        ];
    }

    private function buildConfigFields(Get $get): array
    {
        $adapterCode = $get('adapter');
        $type = $get('type');

        if (!$adapterCode || !$type) {
            return [];
        }

        /** @var AdapterRegistry $registry */
        $registry = app(AdapterRegistry::class);

        try {
            $adapter = $registry->getAdapter($adapterCode, $type);
        } catch (\Throwable) {
            return [];
        }

        $schema = $adapter->configSchema();
        $fields = [];

        foreach ($schema as $key => $def) {
            $fields[] = $this->makeConfigField("config.{$key}", $key, $def);
        }

        return $fields;
    }

    private function makeConfigField(string $name, string $key, array $def): Component
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

            'multiselect' => CheckboxList::make($name)
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
                ->label($label)
                ->placeholder($def['placeholder'] ?? null)
                ->default($def['default'] ?? null)
                ->required($required)
                ->helperText($helperText),
        };
    }
}
