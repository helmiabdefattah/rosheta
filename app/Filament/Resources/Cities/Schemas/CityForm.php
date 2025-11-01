<?php

namespace App\Filament\Resources\Cities\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Basic Information')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('name')
                                        ->label('Name (English)')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('name_ar')
                                        ->label('Name (Arabic)')
                                        ->required()
                                        ->maxLength(255),
                                ]),
                            Select::make('governorate_id')
                                ->label('Governorate')
                                ->relationship('governorate', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('sort_order')
                                        ->label('Sort Order')
                                        ->numeric()
                                        ->default(0),
                                    Toggle::make('is_active')
                                        ->label('Is Active')
                                        ->default(true),
                                ]),
                        ]),
                ])
                    ->columnSpanFull()
                    ->maxWidth(Width::Full),
            ]);
    }
}
