<?php

namespace App\Filament\Resources\Offers\Schemas;

use App\Models\ClientRequest;
use App\Models\Pharmacy;
use App\Models\Medicine;
use Filament\Forms;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // ✅ Section 1 - Offer Details (full-width)
            Section::make('Offer Details')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Select::make('client_request_id')
                        ->label('Client Request')
                        ->options(fn() => ClientRequest::with('client')->get()
                            ->mapWithKeys(fn($r) => [$r->id => "Request #{$r->id} - {$r->client->name}"]))
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                            if (!$state) {
                                $set('lines', []);
                                $set('total_price', null);
                                return;
                            }

                            $request = \App\Models\ClientRequest::with('lines.medicine')->find($state);
                            $lines = $request?->lines?->map(fn($line) => [
                                'medicine_id' => $line->medicine_id,
                                'quantity' => $line->quantity,
                                'unit' => $line->unit,
                                'price' => $line->price,
                            ])->toArray() ?? [];

                            $set('lines', $lines);

                            $total = collect($lines)->reduce(fn($carry, $line) =>
                                $carry + ((int)($line['quantity'] ?? 0) * (float)($line['price'] ?? 0))
                                , 0);
                            $set('total_price', number_format($total, 2, '.', ''));
                        }),

                    Forms\Components\Select::make('pharmacy_id')
                        ->label('Pharmacy')
                        ->options(fn() => Pharmacy::pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'submitted' => 'Submitted',
                            'accepted' => 'Accepted',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('draft')
                        ->required(),

                    Forms\Components\TextInput::make('total_price')
                        ->numeric()
                        ->suffix('EGP')
                        ->nullable()
                        ->readOnly(),
                ])
                ->columns(2)
            ,

            // ✅ Section 2 - Request Images (full-width)
            Section::make('Request Images')
                ->columnSpanFull()
                ->schema([
                    ViewField::make('request_images')
                        ->columnSpanFull()
                        ->view('filament.offers.request-images')
                        ->viewData(fn(Get $get) => [
                            'images' => optional(ClientRequest::find($get('client_request_id')))->images ?? [],
                        ]),
                ]),

            // ✅ Section 3 - Client Request Lines (full-width)
            Section::make('Client Request Lines')
                ->columnSpanFull()
                ->schema([
                    ViewField::make('request_lines_list')
                        ->columnSpanFull()
                        ->view('filament.offers.request-lines')
                        ->reactive()
                        ->viewData(function (Get $get) {
                            $requestId = $get('client_request_id');
                            if (!$requestId) {
                                return ['requestLines' => []];
                            }

                            $request = ClientRequest::with('lines.medicine')->find($requestId);
                            if (!$request || !$request->lines) {
                                return ['requestLines' => []];
                            }

                            $requestLines = $request->lines->map(function ($line) {
                                return [
                                    'medicine_id' => $line->medicine_id,
                                    'medicine_name' => $line->medicine->name ?? 'N/A',
                                    'quantity' => $line->quantity,
                                    'unit' => $line->unit,
                                    'price' => '', // Price will be filled in offer line
                                ];
                            })->toArray();

                            return [
                                'requestLines' => $requestLines,
                                'repeaterId' => 'requestLines',
                            ];
                        }),
                ])
                ->collapsible()
                ->collapsed(false),

            // ✅ Section 4 - Offer Lines (full-width)
            Section::make('Offer Lines')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Repeater::make('requestLines')
                        ->relationship('requestLines') // points to OfferLine (HasMany)
                        ->schema([
                            Forms\Components\Select::make('medicine_id')
                                ->label('Medicine')
                                ->relationship('medicine', 'name')
                                ->columnSpanFull()
                                ->searchable()
                                ->required(),

                            Forms\Components\TextInput::make('quantity')
                                ->numeric()
                                ->minValue(1)
                                ->columnSpanFull()
                                ->required(),

                            Forms\Components\Select::make('unit')
                                ->columnSpanFull()
                                ->options([
                                    'box' => 'Box',
                                    'strips' => 'Strips',
                                    'bottle' => 'Bottle',
                                    'pack' => 'Pack',
                                    'piece' => 'Piece',
                                ])
                                ->required(),

                            Forms\Components\TextInput::make('price')
                                ->numeric()
                                ->suffix('EGP')
                                ->columnSpanFull()
                                ->required(),
                        ])
                        ->default(function (Get $get) {
                            $requestId = $get('client_request_id');
                            if (!$requestId) return [];

                            $request = \App\Models\ClientRequest::with('lines.medicine')->find($requestId);

                            return $request?->lines?->map(fn($line) => [
                                'medicine_id' => $line->medicine_id,
                                'quantity' => $line->quantity,
                                'unit' => $line->unit,
                                'price' => $line->price,
                            ])->toArray() ?? [];
                        })
                        ->columns(2)
                        ->minItems(1),
                ])
                ->collapsible(),
        ]);
    }
}
