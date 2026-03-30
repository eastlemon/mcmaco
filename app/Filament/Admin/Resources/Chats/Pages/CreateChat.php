<?php

namespace App\Filament\Admin\Resources\Chats\Pages;

use App\Filament\Admin\Resources\Chats\ChatResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChat extends CreateRecord
{
    protected static string $resource = ChatResource::class;
}
