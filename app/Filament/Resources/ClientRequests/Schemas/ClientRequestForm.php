<?php

namespace App\Filament\Resources\ClientRequests\Schemas;

use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Medicine;
use Filament\Forms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Form;
use Filament\Support\Enums\Width;
use Filament\Schemas\Components\Utilities\Get;

class ClientRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Request Details')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Forms\Components\Select::make('client_id')
                                        ->label('Client')
                                        ->options(fn () => Client::pluck('name', 'id'))
                                        ->searchable()
                                        ->required()
                                        ->reactive(),

                                    Forms\Components\Select::make('client_address_id')
                                        ->label('Address')
                                        ->options(function (Get $get) {
                                            $clientId = $get('client_id');
                                            if (! $clientId) {
                                                return [];
                                            }
                                            return \App\Models\ClientAddress::where('client_id', $clientId)->pluck('address', 'id');
                                        })
                                        ->searchable()
                                        ->required(),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    Forms\Components\Toggle::make('pregnant')->default(false),
                                    Forms\Components\Toggle::make('diabetic')->default(false),
                                    Forms\Components\Toggle::make('heart_patient')->default(false),
                                    Forms\Components\Toggle::make('high_blood_pressure')->default(false),
                                ]),
                            Forms\Components\Textarea::make('note')->rows(3),

                            Forms\Components\Select::make('status')
                                ->options([
                                    'pending' => 'Pending',
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                ])->default('pending')
                                ->required(),
                        ]),

                    Section::make('Medicines')
                        ->schema([
                            Forms\Components\Repeater::make('lines')
                                ->relationship()
                                ->schema([
                                    Forms\Components\Select::make('medicine_id')
                                        ->label('Medicine')
                                        ->relationship('medicine', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    Forms\Components\TextInput::make('quantity')
                                        ->numeric()
                                        ->minValue(1)
                                        ->required(),
                                    Forms\Components\Select::make('unit')
                                        ->options([
                                            'box' => 'Box',
                                            'strips' => 'Strips',
                                        ])
                                        ->required(),
                                ])
                                ->addActionLabel('Add medicine')
                                ->columns(3)
                                ->minItems(1),
                        ])
                        ->collapsible(),
                ])
                    ->columnSpanFull()
                    ->maxWidth(Width::Full),
            ]);
    }
}



