<?php

namespace App\Filament\Resources\Offers\RelationManagers;

use App\Models\Medicine;
use App\Models\MedicalTest;
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
    protected static string $relationship = 'lines'; // Use the dynamic lines() relationship in Offer

    protected static ?string $title = 'Offer Lines';

    public function form(Schema $schema): Schema
    {
        $requestType = $this->getOwnerRecord()->request_type;

        if ($requestType === 'test') {
            return $schema->components([
                Forms\Components\Select::make('medical_test_id')
                    ->label('Medical Test')
                    ->options(fn () => MedicalTest::orderBy('test_name_en')
                        ->get()
                        ->mapWithKeys(fn ($test) => [$test->id => "{$test->test_name_en} / {$test->test_name_ar}"]))
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->label('Price')
                    ->suffix('EGP')
                    ->required(),

                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->nullable(),
            ]);
        }

        // Medicine
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
                ->label('Price')
                ->suffix('EGP')
                ->required(),

            Forms\Components\Textarea::make('notes')
                ->label('Notes')
                ->nullable(),
        ]);
    }

    public function table(Table $table): Table
    {
        $requestType = $this->getOwnerRecord()->request_type;

        if ($requestType === 'test') {
            return $table
                ->columns([
                    TextColumn::make('medicalTest.test_name_en')->label('Test Name (EN)'),
                    TextColumn::make('medicalTest.test_name_ar')->label('Test Name (AR)'),
                    TextColumn::make('price')->label('Price')->money('EGP'),
                    TextColumn::make('notes'),
                ])
                ->headerActions([CreateAction::make()->label('Add Test')])
                ->actions([EditAction::make(), DeleteAction::make()])
                ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
        }

        // Medicine
        return $table
            ->columns([
                TextColumn::make('medicine.name')->label('Medicine')->searchable(),
                TextColumn::make('quantity'),
                TextColumn::make('unit'),
                TextColumn::make('price')->label('Price')->money('EGP'),
                TextColumn::make('line_total')->label('Line Total')->money('EGP')
                    ->getStateUsing(fn ($record) => $record->quantity * $record->price),
                TextColumn::make('notes'),
            ])
            ->headerActions([CreateAction::make()->label('Add Medicine')])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}






