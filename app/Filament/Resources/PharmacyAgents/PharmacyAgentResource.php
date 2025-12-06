<?php

namespace App\Filament\Resources\PharmacyAgents;

use App\Filament\Resources\PharmacyAgents\Pages\CreatePharmacyAgent;
use App\Filament\Resources\PharmacyAgents\Pages\EditPharmacyAgent;
use App\Filament\Resources\PharmacyAgents\Pages\ListPharmacyAgents;
use App\Filament\Resources\PharmacyAgents\Tables\PharmacyAgentsTable;
use App\Models\PharmacyAgent;
use App\Models\Pharmacy;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PharmacyAgentResource extends Resource
{
    protected static ?string $model = PharmacyAgent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('pharmacy_id')
                ->label('Pharmacy')
                ->options(fn () => Pharmacy::pluck('name', 'id'))
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('phone'),
            Forms\Components\TextInput::make('email')->email(),
            Forms\Components\Toggle::make('active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return PharmacyAgentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPharmacyAgents::route('/'),
            'create' => CreatePharmacyAgent::route('/create'),
            'edit' => EditPharmacyAgent::route('/{record}/edit'),
        ];
    }
}












