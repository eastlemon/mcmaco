<?php

namespace App\Filament\Admin\Resources\Ads\Pages;

use App\Filament\Admin\Resources\Ads\AdResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAd extends EditRecord
{
    protected static string $resource = AdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
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