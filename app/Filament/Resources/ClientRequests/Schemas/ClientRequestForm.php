<?php

namespace App\Filament\Resources\ClientRequests\Schemas;

use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Medicine;
use App\Models\MedicalTest;
use Filament\Forms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Form;
use Filament\Support\Enums\Width;
use Filament\Schemas\Components\Utilities\Get;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ClientRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /* -------------------------
                  MAIN REQUEST INFO
                --------------------------*/
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
                                            return ClientAddress::where('client_id', $clientId)->pluck('address', 'id');
                                        })
                                        ->searchable()
                                        ->required(),
                                ]),

                            Grid::make(2)
                                ->schema([
                                    Forms\Components\Toggle::make('pregnant')->default(false),
                                    Forms\Components\Toggle::make('diabetic')->default(false),
                                    Forms\Components\Toggle::make('heart_patient')->default(false),
                                    Forms\Components\Toggle::make('high_blood_pressure')->default(false),
                                ]),

                            Forms\Components\Textarea::make('note')->rows(3),

                            /* --- File uploads --- */
                            Forms\Components\FileUpload::make('images')
                                ->label('Upload Prescription Images')
                                ->multiple()
                                ->maxFiles(10)
                                ->directory('requests')
                                ->disk('public')
                                ->preserveFilenames(false)
                                ->getUploadedFileNameForStorageUsing(
                                    function (TemporaryUploadedFile $file, Get $get): string {
                                        $requestId = $get('id');
                                        $ext = $file->getClientOriginalExtension();

                                        if (!$requestId) {
                                            return 'temp-' . uniqid() . ".$ext";
                                        }

                                        return "{$requestId}-" . uniqid() . ".$ext";
                                    }
                                )
                                ->image()
                                ->maxSize(2048)
                                ->helperText('Maximum 10 images, 2MB each.')
                                ->reorderable()
                                ->appendFiles()
                                ->columnSpanFull(),

                            Forms\Components\Select::make('status')
                                ->options([
                                    'pending' => 'Pending',
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                ])
                                ->default('pending')
                                ->required()
                                ->hiddenOn('create'),
                        ]),
                ])->columnSpanFull()->maxWidth(Width::Full),

                /* -------------------------
                  MEDICINES LINES
                --------------------------*/
                Section::make('Medicines')
                    ->schema([
                        Forms\Components\Repeater::make('medicinesLines')
                            ->relationship('medicinesLines')
                            ->schema([

                                Forms\Components\Select::make('medicine_id')
                                    ->label('Medicine')
                                    ->relationship('medicine', 'name')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),

                                Forms\Components\Select::make('unit')
                                    ->options([
                                        'box' => 'Box',
                                        'strips' => 'Strips',
                                        'bottle' => 'Bottle',
                                        'pack' => 'Pack',
                                        'piece' => 'Piece',
                                    ])
                                    ->required(),
                            ])
                            ->addActionLabel('Add medicine')
                            ->columns(1)
                            ->minItems(0),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                /* -------------------------
                  MEDICAL TESTS LINES
                --------------------------*/
                Section::make('Medical Tests')
                    ->schema([
                        Forms\Components\Repeater::make('medicalTestsLines')
                            ->relationship('medicalTestsLines')
                            ->schema([
                                Forms\Components\Select::make('medical_test_id')
                                    ->label('Medical Test')
                                    ->relationship('medicalTest', 'test_name_en')
                                    ->searchable()
                                    ->getSearchResultsUsing(function (string $search): array {
                                        return MedicalTest::query()
                                            ->where('test_name_en', 'like', "%{$search}%")
                                            ->orWhere('test_name_ar', 'like', "%{$search}%")
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(fn ($t) => [
                                                $t->id => trim($t->test_name_en . ' / ' . $t->test_name_ar)
                                            ])
                                            ->toArray();
                                    })
                                    ->getOptionLabelUsing(function ($value): ?string {
                                        $t = MedicalTest::find($value);
                                        return $t ? trim($t->test_name_en . ' / ' . $t->test_name_ar) : null;
                                    })
                                    ->preload(),
                            ])
                            ->addActionLabel('Add medical test')
                            ->columns(1)
                            ->minItems(0),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

            ]);
    }
}
