<?php

namespace App\Filament\Resources\Offers\Schemas;

use App\Models\ClientRequest;
use App\Models\Pharmacy;
use App\Models\Laboratory;
use App\Models\Medicine;
use App\Models\MedicalTest;
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

            // ------------------- Offer Details -------------------
            Section::make('Offer Details')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Select::make('client_request_id')
                        ->label('Client Request')
                        ->options(fn() => ClientRequest::with('client')->get()
                            ->mapWithKeys(fn($r) => [$r->id => "Request #{$r->id} - {$r->client->name}"])->toArray())
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                            $request = ClientRequest::with('lines.medicine', 'lines.medicalTest')->find($state);
                            if (!$request) return;

                            // Prepare offer lines based on type
                            if ($request->type === 'medicine') {
                                $lines = $request->lines->map(fn($line) => [
                                    'medicine_id' => $line->medicine_id,
                                    'quantity' => $line->quantity,
                                    'unit' => $line->unit,
                                    'price' => $line->price,
                                ])->toArray();
                            } elseif ($request->type === 'test') {
                                $lines = $request->lines->map(fn($line) => [
                                    'medical_test_id' => $line->medical_test_id,
                                    'price' => $line->price ?? null, // optional
                                ])->toArray();
                            }

                            $set('lines', $lines);

                            // total price for medicine only
                            if ($request->type === 'medicine') {
                                $total = collect($lines)->reduce(fn($carry, $line) =>
                                    $carry + ((int)($line['quantity'] ?? 0) * (float)($line['price'] ?? 0)), 0
                                );
                                $set('total_price', number_format($total, 2, '.', ''));
                            } else {
                                $set('total_price', null);
                            }
                        }),

                    // ------------------- Pharmacy / Laboratory -------------------
                    Forms\Components\Select::make('pharmacy_id')
                        ->label('Pharmacy')
                        ->options(fn() => Pharmacy::pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->hidden(fn(Get $get) => optional(ClientRequest::find($get('client_request_id')))->type === 'test'),

                    Forms\Components\Select::make('laboratory_id')
                        ->label('Laboratory')
                        ->options(fn() => Laboratory::pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->hidden(fn(Get $get) => optional(ClientRequest::find($get('client_request_id')))->type === 'medicine')
                        ->disabled(fn() => auth()->user()?->laboratory_id !== null)
                        ->default(fn() => auth()->user()?->laboratory_id),

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
                ->columns(2),

            // ------------------- Request Images -------------------
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

            // ------------------- Offer Lines -------------------
            Section::make('Offer Lines')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Repeater::make('lines')
                        ->relationship('lines') // HasMany to OfferLine
                        ->schema(function(Get $get) {
                            $request = ClientRequest::find($get('client_request_id'));
                            if (!$request) return [];

                            if ($request->type === 'medicine') {
                                return [
                                    Forms\Components\Select::make('medicine_id')
                                        ->label('Medicine')
                                        ->relationship('medicine', 'name')
                                        ->searchable()
                                        ->required(),

                                    Forms\Components\TextInput::make('quantity')
                                        ->numeric()
                                        ->minValue(1)
                                        ->required(),

                                    Forms\Components\Select::make('unit')
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
                                        ->required(),
                                ];
                            } else {
                                // Medical Test only
                                return [
                                    Forms\Components\Select::make('medical_test_id')
                                        ->label('Medical Test')
                                        ->relationship('medicalTest', 'test_name_en')
                                        ->searchable()
                                        ->required(),

                                    Forms\Components\TextInput::make('price')
                                        ->numeric()
                                        ->suffix('EGP')
                                        ->nullable(),
                                ];
                            }
                        })
                        ->default(function(Get $get) {
                            $request = ClientRequest::with('lines.medicine', 'lines.medicalTest')->find($get('client_request_id'));
                            if (!$request) return [];

                            return $request->lines->map(fn($line) => $request->type === 'medicine'
                                ? [
                                    'medicine_id' => $line->medicine_id,
                                    'quantity' => $line->quantity,
                                    'unit' => $line->unit,
                                    'price' => $line->price,
                                ]
                                : [
                                    'medical_test_id' => $line->medical_test_id,
                                    'price' => $line->price ?? null,
                                ])->toArray();
                        })
                        ->columns(2)
                        ->minItems(1)
                        ->reactive(),
                ])
                ->collapsible(),

        ]);
    }
}
