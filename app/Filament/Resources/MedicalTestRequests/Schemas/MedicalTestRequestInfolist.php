<?php

namespace App\Filament\Resources\MedicalTestRequests\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Schema;

class MedicalTestRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('client.name')->label('Client'),
                TextEntry::make('address.address')->label('Address'),
                TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextEntry::make('note')->label('Note')->columnSpanFull(),
                TextEntry::make('tests_list')
                    ->label('Medical Tests')
                    ->state(function ($record) {
                        if (!$record?->lines || $record->lines->isEmpty()) {
                            return 'No medical tests added.';
                        }
                        $tests = [];
                        foreach ($record->lines as $line) {
                            $tests[] = '• ' . ($line->medicalTest->test_name_en ?? 'Unknown Test');
                        }
                        return implode("\n", $tests);
                    })
                    ->markdown()
                    ->columnSpanFull(),
                ImageEntry::make('images')
                    ->label('Attached Images')
                    ->stacked()
                    ->size('xl')
                    ->limit(5)
                    ->limitedRemainingText()
                    ->columnSpanFull()
                    ->hidden(fn ($record) => empty($record->images)),
            ]);
    }
}


