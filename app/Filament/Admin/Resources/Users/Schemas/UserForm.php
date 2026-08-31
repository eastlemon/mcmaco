<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label(__('filament.users.fields.email'))
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn ($record) => $record === null)
                    ->minLength(6),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('city')
                    ->maxLength(255),
                TextInput::make('avatar')
                    ->helperText(__('filament.users.hints.storage_path')),
                Textarea::make('bio')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
