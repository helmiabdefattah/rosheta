<?php

namespace App\Filament\Resources\ClientRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use App\Filament\Resources\Offers\OfferResource;
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
            ->modifyQueryUsing(fn ($query) => $query->with(['client', 'address']))
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('makeOffer')
                    ->label('Make Offer')
                    ->icon('heroicon-o-currency-dollar')
                    ->url(fn ($record) => route('offers.create', ['request' => $record->id])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}


