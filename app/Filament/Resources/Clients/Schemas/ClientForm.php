<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Width;
use Dotswan\MapPicker\Fields\Map;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use App\Models\City;
use App\Models\Area;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    // === Basic Information ===
                    Section::make('Client Information')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Client Name')
                                        ->required()
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('phone_number')
                                        ->label('Phone Number')
                                        ->tel()
                                        ->required()
                                        ->maxLength(20),
                                ]),
                        ]),

                    // === Addresses with Map ===
//                    Section::make('Addresses')
//                        ->schema([
//                            Forms\Components\Repeater::make('addresses')
//                                ->relationship() // hasMany(ClientAddress::class)
//                                ->label('Client Addresses')
//                                ->schema([
//                                    Forms\Components\TextInput::make('address')
//                                        ->label('Address')
//                                        ->required(),
//
//
//                                    // 🏙️ Select City
//                                    Forms\Components\Select::make('city_id')
//                                        ->label('City')
//                                        ->options(fn () => City::pluck('name', 'id'))
//                                        ->searchable()
//                                        ->reactive()
//                                        ->required(),
//
//                                    // 🏘️ Select Area (filtered by selected City)
//                                    Forms\Components\Select::make('area_id')
//                                        ->label('Area')
//                                        ->options(function (Get $get) {
//                                            $cityId = $get('city_id');
//                                            if (!$cityId) {
//                                                return [];
//                                            }
//                                            return Area::where('city_id', $cityId)->pluck('name', 'id');
//                                        })
//                                        ->reactive()
//                                        ->required(),
//
//                                    // 👇 Display lat/lng (read-only for user feedback)
//                                    Forms\Components\TextInput::make('lat')
//                                        ->label('Latitude')
//                                        ->readOnly()
//                                        ->dehydrated(false)
//                                        ->extraAttributes(['style' => 'background-color:#f9f9f9; color:#555;']),
//
//                                    Forms\Components\TextInput::make('lng')
//                                        ->label('Longitude')
//                                        ->readOnly()
//                                        ->dehydrated(false)
//                                        ->extraAttributes(['style' => 'background-color:#f9f9f9; color:#555;']),
//
//                                    // 🗺️ Map Picker
//                                    Map::make('map')
//                                        ->defaultLocation(latitude: 30.044605, longitude: 31.232140)
//                                        ->showMarker(true)
//                                        ->clickable(true)
//                                        ->zoom(12)
//                                        ->tilesUrl("https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}")
//                                        ->afterStateUpdated(function (Set $set, ?array $state): void {
//                                            if ($state) {
//                                                $set('lat', $state['lat']);
//                                                $set('lng', $state['lng']);
//                                            }
//                                        })
//                                        ->afterStateHydrated(function ($state, $record, Set $set): void {
//                                            if ($record && $record->location) {
//                                                $loc = is_string($record->location)
//                                                    ? json_decode($record->location, true)
//                                                    : $record->location;
//
//                                                if (is_array($loc)) {
//                                                    $set('map', $loc);
//                                                    $set('lat', $loc['lat'] ?? null);
//                                                    $set('lng', $loc['lng'] ?? null);
//                                                }
//                                            }
//                                        })
//                                        ->extraStyles([
//                                            'min-height: 50vh',
//                                            'border-radius: 20px',
//                                            'margin-top: 1rem',
//                                        ]),
//
//                                    // 🧩 Store lat/lng into "location" JSON column
//                                    Forms\Components\Hidden::make('location')
//                                        ->dehydrateStateUsing(function (Get $get) {
//                                            $lat = $get('lat');
//                                            $lng = $get('lng');
//
//                                            if (!$lat || !$lng) {
//                                                return null;
//                                            }
//
//                                            return [
//                                                'lat' => (float) $lat,
//                                                'lng' => (float) $lng,
//                                            ];
//                                        })
//                                        ->dehydrated(true)
//                                        ->nullable(),
//                                ])
//                                ->minItems(1)
//                                ->createItemButtonLabel('Add Another Address')
//                                ->columns(1),
//                        ])
//                        ->collapsible(),
                ])
                    ->columnSpanFull()
                    ->maxWidth(Width::Full),
            ]);
    }
}
