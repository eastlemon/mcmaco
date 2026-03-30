<?php

namespace App\Filament\Admin\Resources\Messages\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('chat_id')
                    ->relationship('chat', 'id')
                    ->searchable()
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Textarea::make('message')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                Toggle::make('is_read'),
                DateTimePicker::make('read_at'),
            ]);
    }
}
