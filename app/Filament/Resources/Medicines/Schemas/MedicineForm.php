<?php

namespace App\Filament\Resources\Medicines\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;

class MedicineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    // All form sections here
                    Section::make('Basic Information')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                TextInput::make('api_id')
                                    ->label('API ID'),
                                TextInput::make('name')
                                    ->label('Name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('arabic')
                                    ->label('Arabic Name')
                                    ->maxLength(255),
                                TextInput::make('company')
                                    ->label('Company')
                                    ->maxLength(255),
                            ]),
                    ]),
                
                Section::make('Pricing & Availability')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('price')
                                    ->label('Price')
                                    ->numeric()
                                    ->prefix('EGP'),
                                TextInput::make('oldprice')
                                    ->label('Old Price')
                                    ->numeric()
                                    ->prefix('EGP'),
                                TextInput::make('newprice')
                                    ->label('New Price')
                                    ->numeric()
                                    ->prefix('EGP'),
                                TextInput::make('availability')
                                    ->label('Availability')
                                    ->numeric()
                                    ->default(0),
                                TextInput::make('shortage')
                                    ->label('Shortage'),
                            ]),
                    ]),
                
                Section::make('Medical Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('active_ingredient')
                                    ->label('Active Ingredient')
                                    ->maxLength(255),
                                TextInput::make('dosage_form')
                                    ->label('Dosage Form')
                                    ->maxLength(255),
                                TextInput::make('route')
                                    ->label('Route')
                                    ->maxLength(255),
                                TextInput::make('dose')
                                    ->label('Dose'),
                            ]),
                        Textarea::make('pharmacology')
                            ->label('Pharmacology')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('uses')
                            ->label('Uses')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
                
                Section::make('Additional Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('units')
                                    ->label('Units'),
                                TextInput::make('small_unit')
                                    ->label('Small Unit'),
                                TextInput::make('sold_times')
                                    ->label('Sold Times')
                                    ->numeric()
                                    ->default(0),
                                TextInput::make('barcode')
                                    ->label('Barcode'),
                                TextInput::make('barcode2')
                                    ->label('Barcode 2'),
                                TextInput::make('qrcode')
                                    ->label('QR Code'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('visits')
                                    ->label('Visits')
                                    ->numeric()
                                    ->default(0),
                                TextInput::make('shares')
                                    ->label('Shares')
                                    ->numeric()
                                    ->default(0),
                            ]),
                    ])
                    ->collapsible(),
                
                Section::make('Flags & Status')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Toggle::make('in_eye')
                                    ->label('In Eye'),
                                Toggle::make('fame')
                                    ->label('Famous'),
                                Toggle::make('cosmo')
                                    ->label('Cosmo'),
                                Toggle::make('repeated')
                                    ->label('Repeated'),
                                Toggle::make('reported')
                                    ->label('Reported'),
                            ]),
                    ])
                    ->collapsible(),
                
                Section::make('System Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('imported')
                                    ->label('Imported'),
                                TextInput::make('search_term')
                                    ->label('Search Term'),
                                TextInput::make('batch_number')
                                    ->label('Batch Number')
                                    ->numeric(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
                ])
                    ->columnSpanFull(),
            ]);
    }
}
