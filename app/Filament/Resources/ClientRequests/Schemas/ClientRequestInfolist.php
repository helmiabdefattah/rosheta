<?php

namespace App\Filament\Resources\ClientRequests\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class ClientRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('client.name')
                    ->label('Client'),

                TextEntry::make('address.address')
                    ->label('Address'),

                TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                IconEntry::make('pregnant')
                    ->boolean()
                    ->label('Pregnant'),

                IconEntry::make('diabetic')
                    ->boolean()
                    ->label('Diabetic'),

                IconEntry::make('heart_patient')
                    ->boolean()
                    ->label('Heart Patient'),

                IconEntry::make('high_blood_pressure')
                    ->boolean()
                    ->label('High Blood Pressure'),

                TextEntry::make('note')
                    ->label('Note')
                    ->columnSpanFull(),

                // Images
                ImageEntry::make('images')
                    ->label('Prescription Images')
                    ->stacked()
                    ->size('xl') // Makes images larger
                    ->limit(5)
                    ->limitedRemainingText()
                    ->columnSpanFull()
                    ->hidden(fn ($record) => empty($record->images)),

                // Medicines List
                TextEntry::make('medicines_list')
                    ->label('Medicines')
                    ->state(function ($record) {
                        if (!$record?->lines || $record->lines->isEmpty()) {
                            return 'No medicines added.';
                        }

                        $medicines = [];
                        foreach ($record->lines as $line) {
                            $medicineName = $line->medicine->name ?? 'Unknown Medicine';
                            $quantity = $line->quantity;
                            $unit = $line->unit;
                            $price = $line->medicine->price ?? 0;

                            if ($unit === 'box') {
                                $lineTotal = $quantity * $price;
                            } elseif ($unit === 'strips') {
                                $unitsPerBox = max($line->medicine->units ?? 1, 1);
                                $lineTotal = $quantity * ($price / $unitsPerBox);
                            } else {
                                $lineTotal = $quantity * $price;
                            }

                            $medicines[] = "• {$medicineName} - {$quantity} {$unit} (" . number_format($lineTotal, 2) . " EGP)";
                        }

                        return implode("\n", $medicines);
                    })
                    ->markdown()
                    ->columnSpanFull(),

                // Total Cost
                TextEntry::make('total_cost')
                    ->label('Total Medicines Cost')
                    ->state(function ($record) {
                        if (!$record?->lines || $record->lines->isEmpty()) {
                            return '0.00 EGP';
                        }

                        $total = 0;
                        foreach ($record->lines as $line) {
                            $medicine = $line->medicine;
                            if (!$medicine) continue;

                            if ($line->unit === 'box') {
                                $total += $line->quantity * $medicine->price;
                            } elseif ($line->unit === 'strips') {
                                $unitsPerBox = max($medicine->units ?? 1, 1);
                                $total += $line->quantity * ($medicine->price / $unitsPerBox);
                            } else {
                                $total += $line->quantity * $medicine->price;
                            }
                        }

                        return number_format($total, 2) . ' EGP';
                    })
                    ->extraAttributes([
                        'style' => 'font-weight:600; color:#10b981; font-size:1.1rem; background-color:#f0fdf4; padding:12px; border-radius:8px;'
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
