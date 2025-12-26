<?php

namespace App\Filament\Resources\Laboratories;

use App\Filament\Resources\Laboratories\Pages\CreateLaboratory;
use App\Filament\Resources\Laboratories\Pages\EditLaboratory;
use App\Filament\Resources\Laboratories\Pages\ListLaboratories;
use App\Filament\Resources\Laboratories\Schemas\LaboratoryForm;
use App\Filament\Resources\Laboratories\Tables\LaboratoriesTable;
use App\Models\Laboratory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LaboratoryResource extends Resource
{
    protected static ?string $model = Laboratory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?string $navigationLabel = 'Laboratories';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return LaboratoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LaboratoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLaboratories::route('/'),
            // Create and Edit are now handled by Blade views
            // 'create' => CreateLaboratory::route('/create'),
            // 'edit' => EditLaboratory::route('/{record}/edit'),
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
















