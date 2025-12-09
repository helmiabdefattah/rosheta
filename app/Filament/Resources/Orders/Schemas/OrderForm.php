<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\ClientRequest;
use App\Models\Pharmacy;
use App\Models\Offer;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Order Details')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Select::make('client_request_id')
                        ->label('Client Request')
                        ->options(fn() => ClientRequest::query()->pluck('id', 'id'))
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('pharmacy_id')
                        ->label('Pharmacy')
                        ->options(fn() => Pharmacy::pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('offer_id')
                        ->label('Source Offer')
                        ->options(fn() => Offer::query()->pluck('id', 'id'))
                        ->searchable()
                        ->nullable(),

                    Forms\Components\Select::make('status')
                        ->options([
                            'preparing' => 'Preparing',
                            'delivering' => 'Delivering',
                            'delivered' => 'Delivered',
                        ])
                        ->default('preparing')
                        ->required(),

                    Forms\Components\TextInput::make('payment_method')
                        ->label('Payment Method')
                        ->placeholder('cash / visa / wallet')
                        ->nullable(),

                    Forms\Components\Toggle::make('payed')
                        ->label('Paid')
                        ->default(false),

                    Forms\Components\TextInput::make('total_price')
                        ->numeric()
                        ->suffix('EGP')
                        ->nullable(),
                ])
                ->columns(2),
        ]);
    }
}








