<?php

namespace App\Filament\Admin\Resources\Reports\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('ad_id')
                    ->relationship('ad', 'title')
                    ->searchable()
                    ->required(),
                Select::make('reporter_user_id')
                    ->relationship('reporter', 'name')
                    ->searchable()
                    ->required(),
                Select::make('reason')
                    ->options([
                        'spam' => 'Spam',
                        'prohibited' => 'Prohibited item',
                        'wrong_category' => 'Wrong category',
                        'other' => 'Other',
                    ])
                    ->required(),
                Textarea::make('comment')
                    ->rows(4)
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'resolved' => 'Resolved',
                    ])
                    ->required()
                    ->default('pending'),
            ]);
    }
}
