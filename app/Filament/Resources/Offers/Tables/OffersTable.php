<?php

namespace App\Filament\Resources\Offers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OffersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('request.id')->label('Request'),
                TextColumn::make('agent.name')->label('Agent'),
                TextColumn::make('status')->badge(),
                TextColumn::make('total_price')->money('EGP')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
//            ->modifyQueryUsing(fn (Builder $q) => $q->with(['request', 'agent'])) السطر ده اشوف بتاع ايه
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

























