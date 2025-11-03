<?php

namespace App\Filament\Resources\Clients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Client Name')
                    ->searchable(),

                TextColumn::make('phone_number')
                    ->label('Phone')
                    ->searchable(),

                // 🏠 Client addresses column
                TextColumn::make('addresses.address')
                    ->label('Addresses')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->addresses
                            ? $record->addresses->pluck('address')->join(', ')
                            : '-';
                    })
                    ->wrap()
                    ->limit(80),

                // Cities (aggregated from related addresses)
                TextColumn::make('addresses.city.name')
                    ->label('Cities')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->addresses
                            ? $record->addresses
                                ->map(fn ($addr) => optional($addr->city)->name)
                                ->filter()
                                ->unique()
                                ->join(', ')
                            : '-';
                    })
                    ->wrap()
                    ->limit(80),

                // Areas (aggregated from related addresses)
                TextColumn::make('addresses.area.name')
                    ->label('Areas')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->addresses
                            ? $record->addresses
                                ->map(fn ($addr) => optional($addr->area)->name)
                                ->filter()
                                ->unique()
                                ->join(', ')
                            : '-';
                    })
                    ->wrap()
                    ->limit(80),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['addresses.city', 'addresses.area']))
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
