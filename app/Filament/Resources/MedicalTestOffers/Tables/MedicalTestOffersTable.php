<?php

namespace App\Filament\Resources\MedicalTestOffers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MedicalTestOffersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('medicalTest.test_name_en')->label('Test')->searchable()->sortable(),
                TextColumn::make('laboratory.name')->label('Laboratory')->searchable()->sortable(),
                TextColumn::make('price')->label('Price')->numeric(decimalPlaces: 2),
                TextColumn::make('discount')->label('Discount (%)')->numeric(decimalPlaces: 2),
                TextColumn::make('offer_price')->label('Offer Price')->numeric(decimalPlaces: 2),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}




