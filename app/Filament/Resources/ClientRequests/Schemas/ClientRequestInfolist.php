<?php

namespace App\Filament\Resources\ClientRequests\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ClientRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('client.name')->label('Client'),
                TextEntry::make('address.address')->label('Address'),
                TextEntry::make('status')->badge(),

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

                // 💰 Total Medicines Cost
                TextEntry::make('total_cost')
                    ->label('💰 Total Medicines Cost')
                    ->state(function ($record) {
                        if (! $record?->Lines) {
                            return '0.00';
                        }

                        $total = 0;

                        foreach ($record->Lines as $line) {
                            $medicine = $line->medicine;
                            if (! $medicine) continue;

                            // If unit is "box"
                            if ($line->unit === 'box') {
                                $total += $line->quantity * $medicine->price;
                            }
                            // If unit is "strips"
                            elseif ($line->unit === 'strips') {
                                $total += $line->quantity * ($medicine->price / max($medicine->units, 1));
                            }
                        }

                        return number_format($total, 2) . ' EGP';
                    })
                    ->extraAttributes(['style' => 'font-weight:600; color:#10b981; font-size:1.1rem;'])
                    ->columnSpanFull(),
            ]);
    }
}
