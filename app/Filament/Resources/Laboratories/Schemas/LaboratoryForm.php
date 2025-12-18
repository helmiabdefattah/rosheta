<?php

namespace App\Filament\Resources\Laboratories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Dotswan\MapPicker\Fields\Map;
use Filament\Schemas\Components\Utilities\Set;

class LaboratoryForm
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
                                        ->label('Laboratory Name')
                                        ->required()
                                        ->maxLength(255),
                                    Select::make('user_id')
                                        ->label('Owner')
                                        ->relationship('user', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->required(),
                                            TextInput::make('email')
                                                ->email()
                                                ->required(),
                                        ]),
                                    TextInput::make('phone')
                                        ->label('Phone')
                                        ->tel()
                                        ->maxLength(255),
                                    TextInput::make('email')
                                        ->label('Email')
                                        ->email()
                                        ->maxLength(255),
                                ]),
                            Textarea::make('address')
                                ->label('Address')
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),
                    
                    Section::make('Location Information')
                        ->schema([
                            Select::make('area_id')
                                ->label('Area')
                                ->relationship('area', 'name', fn ($query) => $query->where('is_active', true))
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    Select::make('governorate_id')
                                        ->label('Governorate')
                                        ->options(\App\Models\Governorate::where('is_active', true)->pluck('name', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->reactive()
                                        ->afterStateUpdated(fn ($set) => $set('city_id', null))
                                        ->dehydrated(false),
                                    Select::make('city_id')
                                        ->label('City')
                                        ->options(function ($get) {
                                            $governorateId = $get('governorate_id');
                                            if ($governorateId) {
                                                return \App\Models\City::where('governorate_id', $governorateId)
                                                    ->where('is_active', true)
                                                    ->pluck('name', 'id');
                                            }
                                            return \App\Models\City::where('is_active', true)->pluck('name', 'id');
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->reactive()
                                        ->createOptionForm([    
                                            TextInput::make('name')
                                                ->label('Name (English)')
                                                ->required(),
                                            TextInput::make('name_ar')
                                                ->label('Name (Arabic)')
                                                ->required(),
                                            Select::make('governorate_id')
                                                ->label('Governorate')
                                                ->options(\App\Models\Governorate::where('is_active', true)->pluck('name', 'id'))
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->reactive(),
                                        ])
                                        ->createOptionUsing(function (array $data): int {
                                            return \App\Models\City::create($data)->id;
                                        }),
                                    TextInput::make('name')
                                        ->label('Name (English)')
                                        ->required(),
                                    TextInput::make('name_ar')
                                        ->label('Name (Arabic)')
                                        ->required(),
                                ])
                                ->columnSpanFull(),
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('lat')
                                        ->label('Latitude')
                                        ->numeric()
                                        ->step(0.00000001),
                                    TextInput::make('lng')
                                        ->label('Longitude')
                                        ->numeric()
                                        ->step(0.00000001),
                                ]),
                                Map::make('location')
                                ->defaultLocation(latitude: 30.044605, longitude: 31.232140)
                                ->showMarker(true)
                                ->clickable(true)
                                ->tilesUrl("https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}")
                                ->zoom(12)
                                ->afterStateUpdated(function (Set $set, ?array $state): void {
                                    $set('lat', $state['lat']);
                                    $set('lng', $state['lng']);
                                })
                                ->afterStateHydrated(function ($state, $record, Set $set): void {
                                   if ($record) {
                                    $set('location', [
                                        'lat' => $record->lat,
                                        'lng' => $record->lng,
                                    ]);
                                    }
                                })
                                ->extraStyles([
                                    'min-height: 75vh',
                                    'border-radius: 20px'
                                ]),
                        ]),
                    
                    Section::make('License Information')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('license_number')
                                        ->label('License Number')
                                        ->maxLength(255),
                                    TextInput::make('manager_name')
                                        ->label('Manager Name')
                                        ->maxLength(255),
                                    TextInput::make('manager_license')
                                        ->label('Manager License')
                                        ->maxLength(255),
                                ]),
                        ])
                        ->collapsible(),
                    
                    Section::make('Operating Hours')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TimePicker::make('opening_time')
                                        ->label('Opening Time')
                                        ->seconds(false),
                                    TimePicker::make('closing_time')
                                        ->label('Closing Time')
                                        ->seconds(false),
                                ]),
                        ])
                        ->collapsible(),
                    
                    Section::make('Status')
                        ->schema([
                            Grid::make(1)
                                ->schema([
                                    Toggle::make('is_active')
                                        ->label('Is Active')
                                        ->default(true),
                                    Textarea::make('notes')
                                        ->label('Notes')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ]),
                        ])
                        ->collapsible(),
                ])
                    ->columnSpanFull()
                    ->maxWidth(Width::Full),
            ]);
    }
}
