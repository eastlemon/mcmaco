<?php

namespace App\Filament\Admin\Resources\Chats;

use App\Filament\Admin\Resources\Chats\Pages\CreateChat;
use App\Filament\Admin\Resources\Chats\Pages\EditChat;
use App\Filament\Admin\Resources\Chats\Pages\ListChats;
use App\Filament\Admin\Resources\Chats\Schemas\ChatForm;
use App\Filament\Admin\Resources\Chats\Tables\ChatsTable;
use App\Models\Chat;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChatResource extends Resource
{
    protected static ?string $model = Chat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ChatForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChatsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChats::route('/'),
            'create' => CreateChat::route('/create'),
            'edit' => EditChat::route('/{record}/edit'),
        ];
    }
}
