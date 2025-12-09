<?php

namespace App\Filament\Resources\ClientRequests\Pages;

use App\Filament\Resources\ClientRequests\ClientRequestResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditClientRequest extends EditRecord
{
    protected static string $resource = ClientRequestResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $hasMedicines = (
            (isset($data['lines']) && is_array($data['lines']) && count($data['lines']) > 0) ||
            (isset($data['medicinesLines']) && is_array($data['medicinesLines']) && count($data['medicinesLines']) > 0)
        );
        $hasTests = (
            (isset($data['test_lines']) && is_array($data['test_lines']) && count($data['test_lines']) > 0) ||
            (isset($data['medicalTestsLines']) && is_array($data['medicalTestsLines']) && count($data['medicalTestsLines']) > 0)
        );

        if ($hasMedicines && $hasTests) {
            throw ValidationException::withMessages([
                'medicinesLines' => 'Choose either medicines or medical tests, not both.',
                'medicalTestsLines' => 'Choose either medical tests or medicines, not both.',
            ]);
        }

        if ($hasTests) {
            $data['type'] = 'test';
        } elseif ($hasMedicines) {
            $data['type'] = 'medicine';
        } else {
            $data['type'] = null;
        }

        return $data;
    }
}



