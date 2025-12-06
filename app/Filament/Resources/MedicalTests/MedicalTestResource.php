<?php

namespace App\Filament\Resources\MedicalTests;

use App\Filament\Resources\MedicalTests\Pages\CreateMedicalTest;
use App\Filament\Resources\MedicalTests\Pages\EditMedicalTest;
use App\Filament\Resources\MedicalTests\Pages\ListMedicalTests;
use App\Filament\Resources\MedicalTests\Schemas\MedicalTestForm;
use App\Filament\Resources\MedicalTests\Tables\MedicalTestsTable;
use App\Models\MedicalTest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MedicalTestResource extends Resource
{
    protected static ?string $model = MedicalTest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?string $navigationLabel = 'Medical Tests';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MedicalTestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicalTestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedicalTests::route('/'),
            'create' => CreateMedicalTest::route('/create'),
            'edit' => EditMedicalTest::route('/{record}/edit'),
        ];
    }
}











