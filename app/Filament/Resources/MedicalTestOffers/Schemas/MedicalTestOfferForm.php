<?php

namespace App\Filament\Resources\MedicalTestOffers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MedicalTestOfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([
                Section::make('Offer Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('medical_test_id')
                                    ->label('Medical Test')
                                    ->relationship('medicalTest', 'test_name_en')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('laboratory_id')
                                    ->label('Laboratory')
                                    ->relationship('laboratory', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('price')
                                    ->label('Base Price')
                                    ->numeric()
                                    ->step(0.01)
                                    ->required(),
                                TextInput::make('discount')
                                    ->label('Discount (%)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->default(0),
                                TextInput::make('offer_price')
                                    ->label('Offer Price')
                                    ->numeric()
                                    ->step(0.01),
                            ]),
                    ]),
            ])->columnSpanFull(),
        ]);
    }
}




