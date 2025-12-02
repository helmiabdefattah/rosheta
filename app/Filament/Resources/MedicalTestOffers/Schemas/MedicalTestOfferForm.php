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
                                Select::make('client_request_id')
                                    ->label('Client Request')
                                    ->relationship('clientRequest', 'id')
                                    ->searchable()
                                    ->preload()
                                    ->hiddenOn('create')
                                    ->nullable(),
                                Select::make('medical_test_id')
                                    ->label('Medical Test')
                                    ->relationship('medicalTest', 'test_name_en')
                                    ->searchable()
                                    ->getSearchResultsUsing(function (string $search): array {
                                        return \App\Models\MedicalTest::query()
                                            ->where('test_name_en', 'like', "%{$search}%")
                                            ->orWhere('test_name_ar', 'like', "%{$search}%")
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(fn ($t) => [$t->id => trim($t->test_name_en . ' / ' . $t->test_name_ar)])
                                            ->toArray();
                                    })
                                    ->getOptionLabelUsing(function ($value): ?string {
                                        $t = \App\Models\MedicalTest::find($value);
                                        return $t ? trim($t->test_name_en . ' / ' . $t->test_name_ar) : null;
                                    })
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




