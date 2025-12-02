<?php

namespace App\Filament\Resources\ClientRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClientRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'test' => 'info',
                        'medicine' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('client_label')
                    ->label('Client')
                    ->state(fn ($record) => $record?->client?->name ?? '-'),

                TextColumn::make('address_label')
                    ->label('Address')
                    ->state(fn ($record) => $record?->address?->address ?? '-')
                    ->wrap()
                    ->limit(60),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'medicine' => 'Medicine',
                        'test' => 'Test',
                    ]),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with(['client', 'address', 'lines', 'testLines']))
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


