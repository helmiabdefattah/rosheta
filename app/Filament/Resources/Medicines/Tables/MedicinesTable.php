<?php

namespace App\Filament\Resources\Medicines\Tables;

use App\Models\Medicine;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MedicinesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('arabic')
                    ->label('Arabic Name')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('route')
                    ->label('Route')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: false),
                IconColumn::make('cosmo')
                    ->label('Cosmo')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('reported')
                    ->label('Reported')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('price')
                    ->label('Price')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('oldprice')
                    ->label('Old Price')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('newprice')
                    ->label('New Price')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('active_ingredient')
                    ->label('Active Ingredient')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('company')
                    ->label('Company')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('dosage_form')
                    ->label('Dosage Form')
                    ->searchable()
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                SelectFilter::make('imported')
                    ->options([
                        'local' => 'Local',
                        'imported' => 'Imported',
                    ]),
                SelectFilter::make('dosage_form')
                    ->multiple()
                    ->options(fn () => Medicine::query()
                        ->whereNotNull('dosage_form')
                        ->distinct()
                        ->pluck('dosage_form', 'dosage_form')
                        ->toArray()),
            ])
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
