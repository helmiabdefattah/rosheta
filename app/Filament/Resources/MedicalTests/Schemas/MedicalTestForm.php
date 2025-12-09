<?php

namespace App\Filament\Resources\MedicalTests\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MedicalTestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([
                Section::make('Details')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('test_name_en')->label('Name (EN)')->required()->maxLength(255),
                            TextInput::make('test_name_ar')->label('Name (AR)')->required()->maxLength(255),
                        ]),
                        Textarea::make('test_description')->label('Description')->rows(4)->columnSpanFull(),
                    ]),
            ])->columnSpanFull(),
        ]);
    }
}


