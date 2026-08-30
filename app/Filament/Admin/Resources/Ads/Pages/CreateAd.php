<?php

namespace App\Filament\Admin\Resources\Ads\Pages;

use App\Filament\Admin\Resources\Ads\AdResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAd extends CreateRecord
{
    protected static string $resource = AdResource::class;

    protected function afterCreate(): void
    {
        $this->syncUploadedImages();
    }

    protected function syncUploadedImages(): void
    {
        $paths = $this->form->getRawState()['uploadedImages'] ?? [];

        /** @var \App\Models\Ad */
        $record = $this->getRecord();
        $record->syncImages(is_array($paths) ? $paths : []);
    }
}