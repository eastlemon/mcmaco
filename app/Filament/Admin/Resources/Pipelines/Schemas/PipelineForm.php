<?php

namespace App\Filament\Admin\Resources\Pipelines\Schemas;

use App\Models\Pipeline;
use App\Pipelines\AdapterRegistry;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PipelineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('filament.pipelines.sections.main'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('filament.pipelines.fields.name'))
                        ->required()
                        ->maxLength(255),

                    Select::make('type')
                        ->label(__('filament.pipelines.fields.type'))
                        ->options(Pipeline::TYPES)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set): void {
                            // Explicit reset — the adapter hook chain can be
                            // deduplicated by Filament's state-hash guard, so
                            // do not rely on it for the config reset.
                            $set('adapter', null);
                            $set('config', []);
                        }),

                    Select::make('adapter')
                        ->label(__('filament.pipelines.fields.adapter'))
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
                        ->afterStateUpdated(function (Get $get, Set $set): void {
                            $set('config', self::defaultConfigFor($get('type'), $get('adapter')));
                        }),

                    Select::make('format')
                        ->label(__('filament.pipelines.fields.format'))
                        ->options(Pipeline::FORMATS)
                        ->default('csv')
                        ->required(),
                ]),

            Section::make(__('filament.pipelines.sections.schedule'))
                ->schema([
                    TextInput::make('schedule')
                        ->label(__('filament.pipelines.fields.cron'))
                        ->placeholder(__('filament.pipelines.placeholders.cron'))
                        ->helperText(__('filament.pipelines.hints.cron'))
                        ->maxLength(100)
                        ->regex('/^[\d\/\*\,\-\s]+$/')
                        ->validationMessages([
                            'regex' => __('filament.pipelines.validation.cron_regex'),
                        ]),

                    Toggle::make('is_active')
                        ->label(__('filament.pipelines.fields.active'))
                        ->default(true),
                ]),

            Section::make(__('filament.pipelines.sections.adapter_config'))
                ->schema(fn (Get $get): array => self::buildConfigFields($get))
                ->visible(fn (Get $get): bool => filled($get('adapter'))),
        ]);
    }

    /**
     * Default state for the dynamic adapter config fields.
     *
     * Filament 5 caches child schemas: on mount (no adapter selected) the
     * config fields do not exist, so `fill()` never creates their state
     * paths. When the schema closure re-evaluates after an adapter is
     * selected, the rendered fields entangle to `data.config.*` paths that
     * are missing from the Livewire snapshot — Livewire 4 throws
     * "Livewire property ['data.config.*'] cannot be found" and the inputs
     * stop syncing. Seeding every schema key (defaults, null for files)
     * before the fields render keeps the entangled state intact.
     */
    public static function defaultConfigFor(?string $type, ?string $adapter): array
    {
        if (! $type || ! $adapter) {
            return [];
        }

        /** @var AdapterRegistry $registry */
        $registry = app(AdapterRegistry::class);

        try {
            $schema = $registry->getAdapter($adapter, $type)->configSchema();
        } catch (\Throwable) {
            return [];
        }

        $defaults = [];

        foreach ($schema as $key => $def) {
            // FileUpload normalizes blank state to an empty array; seed the
            // same shape so the entangled state matches what the JS expects.
            $defaults[$key] = ($def['type'] ?? 'text') === 'file'
                ? []
                : ($def['default'] ?? null);
        }

        return $defaults;
    }

    private static function buildConfigFields(Get $get): array
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
            $fields[] = self::makeConfigField("config.{$key}", $key, $def);
        }

        return $fields;
    }

    private static function makeConfigField(string $name, string $key, array $def): Component
    {
        $label = $def['label'] ?? ucfirst($key);
        $type = $def['type'] ?? 'text';
        $required = $def['required'] ?? false;
        $helperText = $def['help'] ?? null;

        return match ($type) {
            'file' => self::makeFileField($name, $def, $label, $required, $helperText),

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

    private static function makeFileField(string $name, array $def, string $label, bool $required, ?string $helperText): FileUpload
    {
        $field = FileUpload::make($name)
            ->label($label)
            ->disk($def['disk'] ?? 'local')
            ->directory($def['directory'] ?? 'pipeline-uploads')
            ->maxSize($def['max_size'] ?? 20480)
            ->panelLayout('integrated')
            ->helperText($helperText)
            ->required($required);

        if (! empty($def['accepted'])) {
            $field->acceptedFileTypes(self::expandAcceptedFileTypes($def['accepted']));
        }

        return $field;
    }

    /**
     * Филамент скармливает acceptedFileTypes() и в HTML accept, и в правило
     * валидации `mimetypes:` без изменений. Голое расширение («.csv») в таком
     * правиле невыполнимо — Symfony гессает MIME по содержимому файла, и он
     * никогда не равен «.csv». Поэтому расширения автоматически дополняем
     * известными MIME-типами: расширение остаётся для файлпикера, MIME
     * обеспечивают проходимость валидации. Прямые MIME проходят как есть.
     *
     * @param  list<string>  $accepted
     * @return list<string>
     */
    private static function expandAcceptedFileTypes(array $accepted): array
    {
        $extensionMimes = [
            'csv' => ['text/csv', 'text/plain', 'application/csv'],
            'zip' => ['application/zip', 'application/x-zip-compressed'],
            'pdf' => ['application/pdf'],
            'txt' => ['text/plain'],
            'json' => ['application/json', 'text/plain'],
            'xlsx' => [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel',
            ],
        ];

        $expanded = [];
        foreach ($accepted as $type) {
            $expanded[] = $type;

            if (str_starts_with($type, '.')) {
                $extension = strtolower(ltrim($type, '.'));
                $expanded = array_merge($expanded, $extensionMimes[$extension] ?? []);
            }
        }

        return array_values(array_unique($expanded));
    }
}