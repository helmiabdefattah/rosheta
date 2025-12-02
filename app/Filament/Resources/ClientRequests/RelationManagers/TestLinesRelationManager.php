<?php

namespace App\Filament\Resources\ClientRequests\RelationManagers;

use App\Models\MedicalTest;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TestLinesRelationManager extends RelationManager
{
    protected static string $relationship = 'testLines';

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([
            Forms\Components\Select::make('medical_test_id')
                ->label('Medical Test')
                ->options(fn () => MedicalTest::orderBy('test_name_en')->pluck('test_name_en', 'id'))
                ->searchable()
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('medicalTest.test_name_en')->label('Medical Test')->searchable(),
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


