<?php

namespace App\Filament\Resources\ClientRequests\RelationManagers;

use App\Models\Medicine;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class RequestLinesRelationManager extends RelationManager
{
	protected static string $relationship = 'lines';

	public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
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
				])
				->required(),
		]);
	}


    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('medicine.name')
                    ->label('Medicine')
                    ->searchable(),

                TextColumn::make('quantity')
                    ->label('Quantity'),

                TextColumn::make('unit')
                    ->label('Unit'),

                TextColumn::make('price')
                    ->label('Price')
                    ->state(function ($record) {
                        $medicine = $record->medicine;
                        if (!$medicine) return 0;

                        if ($record->unit === 'box') {
                            return $medicine->price;
                        }

                        if ($record->unit === 'strips' && $medicine->units > 0) {
                            return round($medicine->price / $medicine->units, 2);
                        }

                        return 0;
                    }),

                TextColumn::make('line_total')
                    ->label('Line Total')
                    ->state(function ($record) {
                        $medicine = $record->medicine;
                        if (!$medicine) return 0;

                        if ($record->unit === 'box') {
                            return $record->quantity * $medicine->price;
                        }

                        if ($record->unit === 'strips' && $medicine->units > 0) {
                            return round($record->quantity * ($medicine->price / $medicine->units), 2);
                        }

                        return 0;
                    }),
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




