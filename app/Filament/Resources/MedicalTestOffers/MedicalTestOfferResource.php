<?php

namespace App\Filament\Resources\MedicalTestOffers;

use App\Filament\Resources\MedicalTestOffers\Pages\CreateMedicalTestOffer;
use App\Filament\Resources\MedicalTestOffers\Pages\EditMedicalTestOffer;
use App\Filament\Resources\MedicalTestOffers\Pages\ListMedicalTestOffers;
use App\Filament\Resources\MedicalTestOffers\RelationManagers\OfferLinesRelationManager;
use App\Filament\Resources\MedicalTestOffers\Schemas\MedicalTestOfferForm;
use App\Filament\Resources\MedicalTestOffers\Tables\MedicalTestOffersTable;
use App\Models\MedicalTestOffer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MedicalTestOfferResource extends Resource
{
    protected static ?string $model = MedicalTestOffer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Medical Test Offers';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return MedicalTestOfferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicalTestOffersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            OfferLinesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedicalTestOffers::route('/'),
            'create' => CreateMedicalTestOffer::route('/create'),
            'edit' => EditMedicalTestOffer::route('/{record}/edit'),
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        // The static::getModel() method resolves the model defined in the $model property.
        $modelClass = static::getModel();

        // Use the resolved class name to call the static count method.
        return (string) $modelClass::count();
    }
}




