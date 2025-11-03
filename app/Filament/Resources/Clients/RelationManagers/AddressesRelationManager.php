<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use App\Models\Area;
use App\Models\City;
use Filament\Forms;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AddressesRelationManager extends RelationManager
{
	protected static string $relationship = 'addresses';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('address')
                ->label('Address')
                ->required(),

            Forms\Components\Select::make('city_id')
                ->label('City')
                ->options(fn() => City::pluck('name', 'id'))
                ->searchable()
                ->reactive()
                ->required(),

            Forms\Components\Select::make('area_id')
                ->label('Area')
                ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                    $cityId = $get('city_id');
                    return $cityId ? Area::where('city_id', $cityId)->pluck('name', 'id') : [];
                })
                ->reactive()
                ->required(),

            Forms\Components\TextInput::make('lat')
                ->label('Latitude')
                ->readOnly()
                ->dehydrated(false),

            Forms\Components\TextInput::make('lng')
                ->label('Longitude')
                ->readOnly()
                ->dehydrated(false),

            \Dotswan\MapPicker\Fields\Map::make('map')
                ->defaultLocation(latitude: 30.044605, longitude: 31.232140)
                ->showMarker(true)
                ->clickable(true)
                ->zoom(12)
                ->afterStateUpdated(function ($set, ?array $state) {
                    if ($state) {
                        $set('lat', $state['lat']);
                        $set('lng', $state['lng']);
                    }
                })
                ->afterStateHydrated(function ($state, $record, $set) {
                    if ($record && $record->location) {
                        $loc = is_string($record->location)
                            ? json_decode($record->location, true)
                            : $record->location;

                        if (is_array($loc)) {
                            $set('map', $loc);
                            $set('lat', $loc['lat'] ?? null);
                            $set('lng', $loc['lng'] ?? null);
                        }
                    }
                }),

            Forms\Components\Hidden::make('location')
                ->dehydrateStateUsing(function ($get) {
                    $lat = $get('lat');
                    $lng = $get('lng');
                    if (!$lat || !$lng) return null;
                    return ['lat' => (float)$lat, 'lng' => (float)$lng];
                }),
        ]);
    }

	public function table(Table $table): Table
	{
		return $table
			->columns([
				TextColumn::make('address')
					->label('Address')
					->wrap(),

				TextColumn::make('city.name')
					->label('City')
					->searchable()
					->sortable(),

				TextColumn::make('area.name')
					->label('Area')
					->searchable()
					->sortable(),

				TextColumn::make('created_at')
					->dateTime()
					->toggleable(isToggledHiddenByDefault: true),
			])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
	}
}


