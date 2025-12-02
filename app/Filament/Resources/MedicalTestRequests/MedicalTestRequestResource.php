<?php

namespace App\Filament\Resources\MedicalTestRequests;

use App\Filament\Resources\MedicalTestRequests\Pages\CreateMedicalTestRequest;
use App\Filament\Resources\MedicalTestRequests\Pages\EditMedicalTestRequest;
use App\Filament\Resources\MedicalTestRequests\Pages\ListMedicalTestRequests;
use App\Filament\Resources\MedicalTestRequests\Pages\ViewMedicalTestRequest;
use App\Filament\Resources\MedicalTestRequests\Schemas\MedicalTestRequestForm;
use App\Filament\Resources\MedicalTestRequests\Schemas\MedicalTestRequestInfolist;
use App\Filament\Resources\MedicalTestRequests\Tables\MedicalTestRequestsTable;
use App\Models\MedicalTestRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class MedicalTestRequestResource extends Resource
{
    protected static ?string $model = MedicalTestRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = 'Medical Test Requests';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return MedicalTestRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicalTestRequestsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MedicalTestRequestInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedicalTestRequests::route('/'),
            'create' => CreateMedicalTestRequest::route('/create'),
            'view' => ViewMedicalTestRequest::route('/{record}'),
            'edit' => EditMedicalTestRequest::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return true;
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
                if (str_starts_with($image, 'temp-')) {
                    $extension = pathinfo($image, PATHINFO_EXTENSION);
                    $newFilename = "{$record->id}-" . ($index + 1) . ".{$extension}";

                    if (Storage::disk('public')->exists("medical-test-requests/{$image}")) {
                        Storage::disk('public')->move("medical-test-requests/{$image}", "medical-test-requests/{$newFilename}");
                        $updatedImages[] = $newFilename;
                    }
                } else {
                    $updatedImages[] = $image;
                }
            }

            $record->updateQuietly(['images' => $updatedImages]);
        }
    }
}




