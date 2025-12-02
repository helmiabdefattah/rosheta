<?php

namespace App\Filament\Resources\MedicalTestRequests\Schemas;

use App\Models\Client;
use Filament\Forms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Form;
use Filament\Support\Enums\Width;
use Filament\Schemas\Components\Utilities\Get;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MedicalTestRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Request Details')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Forms\Components\Select::make('client_id')
                                        ->label('Client')
                                        ->options(fn () => Client::pluck('name', 'id'))
                                        ->searchable()
                                        ->required()
                                        ->reactive(),

                                    Forms\Components\Select::make('client_address_id')
                                        ->label('Address')
                                        ->options(function (Get $get) {
                                            $clientId = $get('client_id');
                                            if (! $clientId) {
                                                return [];
                                            }
                                            return \App\Models\ClientAddress::where('client_id', $clientId)->pluck('address', 'id');
                                        })
                                        ->searchable()
                                        ->required(),
                                ]),
                            Forms\Components\Textarea::make('note')->rows(3),

                            Forms\Components\FileUpload::make('images')
                                ->label('Upload Test Images')
                                ->multiple()
                                ->maxFiles(10)
                                ->directory('medical-test-requests')
                                ->disk('public')
                                ->preserveFilenames(false)
                                ->getUploadedFileNameForStorageUsing(
                                    function (TemporaryUploadedFile $file, Get $get): string {
                                        $requestId = $get('id');
                                        $extension = $file->getClientOriginalExtension();

                                        if (!$requestId) {
                                            return 'temp-' . time() . '-' . uniqid() . '.' . $extension;
                                        }

                                        return "{$requestId}-" . time() . '-' . uniqid() . ".{$extension}";
                                    }
                                )
                                ->image()
                                ->maxSize(2048)
                                ->helperText('Maximum 10 images, 2MB each. Supported formats: JPG, PNG, GIF, WEBP')
                                ->reorderable()
                                ->appendFiles()
                                ->columnSpanFull(),

                            Forms\Components\Select::make('status')
                                ->options([
                                    'pending' => 'Pending',
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                ])->default('pending')
                                ->required()->hiddenOn('create'),
                        ]),

                    Section::make('Medical Tests')
                        ->schema([
                            Forms\Components\Repeater::make('lines')
                                ->relationship()
                                ->schema([
                                    Forms\Components\Select::make('medical_test_id')
                                        ->label('Medical Test')
                                        ->relationship('medicalTest', 'test_name_en')
                                        ->searchable()
                                        ->preload(),
                                ])
                                ->addActionLabel('Add medical test')
                                ->columns(1)
                                ->minItems(1),
                        ])
                        ->collapsible(),
                ])
                    ->columnSpanFull()
                    ->maxWidth(Width::Full),
            ]);
    }
}




