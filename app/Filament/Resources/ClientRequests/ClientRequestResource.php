<?php

namespace App\Filament\Resources\ClientRequests;

use App\Filament\Resources\Cities\RelationManagers\AreasRelationManager;
use App\Filament\Resources\ClientRequests\Pages\CreateClientRequest;
use App\Filament\Resources\ClientRequests\Pages\EditClientRequest;
use App\Filament\Resources\ClientRequests\Pages\ListClientRequests;
use App\Filament\Resources\ClientRequests\Pages\ViewClientRequest;
use App\Filament\Resources\ClientRequests\Schemas\ClientRequestForm;
use App\Filament\Resources\ClientRequests\Schemas\ClientRequestInfolist;
use App\Filament\Resources\ClientRequests\Tables\ClientRequestsTable;
use App\Models\ClientRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class ClientRequestResource extends Resource
{
    protected static ?string $model = ClientRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return ClientRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ClientRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\ClientRequests\RelationManagers\RequestLinesRelationManager::class,
            \App\Filament\Resources\ClientRequests\RelationManagers\TestLinesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClientRequests::route('/'),
            'create' => CreateClientRequest::route('/create'),
            'view' => ViewClientRequest::route('/{record}'),
            'edit' => EditClientRequest::route('/{record}/edit'),
        ];
    }
    public static function afterCreate(CreateRecord $event): void
    {
        self::renameTemporaryImages($event->getRecord());
    }

    public static function afterSave(EditRecord $event): void
    {
        self::renameTemporaryImages($event->getRecord());
    }

    protected static function renameTemporaryImages($record): void
    {
        $images = $record->images;

        if ($images && is_array($images)) {
            $updatedImages = [];

            foreach ($images as $index => $image) {
                // Check if it's a temporary file
                if (str_starts_with($image, 'temp-')) {
                    $extension = pathinfo($image, PATHINFO_EXTENSION);
                    $newFilename = "{$record->id}-" . ($index + 1) . ".{$extension}";

                    // Move and rename the file
                    if (Storage::disk('public')->exists("requests/{$image}")) {
                        Storage::disk('public')->move("requests/{$image}", "requests/{$newFilename}");
                        $updatedImages[] = $newFilename;
                    }
                } else {
                    $updatedImages[] = $image;
                }
            }

            // Update the record with new filenames without triggering events
            $record->updateQuietly(['images' => $updatedImages]);
        }
    }

}



