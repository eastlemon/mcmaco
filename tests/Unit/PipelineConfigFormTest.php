<?php

namespace Tests\Unit;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Tests\TestCase;

/**
 * Covers the configSchema() -> Filament form field mapping in PipelineFormSchema.
 */
class PipelineConfigFormTest extends TestCase
{
    /** @var object anonymous class carrying the trait */
    private object $schemaHost;

    protected function setUp(): void
    {
        parent::setUp();

        $this->schemaHost = new class
        {
            use \App\Filament\Admin\Resources\Pipelines\PipelineFormSchema;
        };
    }

    private function makeField(array $def): object
    {
        $method = new \ReflectionMethod($this->schemaHost, 'makeConfigField');
        $method->setAccessible(true);

        return $method->invoke($this->schemaHost, 'config.test_key', 'test_key', $def);
    }

    public function test_file_type_maps_to_file_upload(): void
    {
        $field = $this->makeField([
            'type' => 'file',
            'label' => 'ZIP-архив с фото',
            'accepted' => ['.zip'],
            'max_size' => 204800,
            'directory' => 'pipeline-uploads',
        ]);

        $this->assertInstanceOf(FileUpload::class, $field);
        $this->assertSame('config.test_key', $field->getName());
    }

    public function test_text_and_select_types_still_map(): void
    {
        $this->assertInstanceOf(TextInput::class, $this->makeField(['type' => 'text', 'label' => 'X']));
        $this->assertInstanceOf(Select::class, $this->makeField(['type' => 'select', 'label' => 'X', 'options' => ['a' => 'A']]));
    }
}