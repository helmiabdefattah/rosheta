<?php

namespace App\Filament\Resources\Offers\RelationManagers;

use App\Models\Medicine;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OfferLinesRelationManager extends RelationManager
{
    protected static string $relationship = 'requestLines';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('medicine_id')
                ->label('Medicine')
                ->options(fn () => Medicine::orderBy('name')->pluck('name', 'id'))
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
        ]);
    }
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('medicine.name')->label('Medicine')->searchable(),
                TextColumn::make('quantity'),
                TextColumn::make('unit'),
                TextColumn::make('price')->label('Price')->money('EGP'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Line'),
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



