<?php

namespace App\Filament\Admin\Resources\Chats\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ChatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('ad_id')
                    ->relationship('ad', 'title')
                    ->searchable()
                    ->required(),
                Select::make('buyer_id')
                    ->relationship('buyer', 'name')
                    ->searchable()
                    ->required(),
                Select::make('seller_id')
                    ->relationship('seller', 'name')
                    ->searchable()
                    ->required(),
                DateTimePicker::make('last_message_at'),
            ]);
    }
}
