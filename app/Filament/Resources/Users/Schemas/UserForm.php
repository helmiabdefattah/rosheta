<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('User')
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('email')
                                ->email()
                                ->maxLength(255)
                                ->nullable(),
                            TextInput::make('phone_number')
                                ->maxLength(50)
                                ->nullable(),
                            TextInput::make('password')
                                ->password()
                                ->maxLength(255)
                                ->autocomplete('new-password')
                                ->dehydrated(fn ($state) => filled($state))
                                ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                ->required(fn (string $operation): bool => $operation === 'create'),
                            Toggle::make('is_active')
                                ->label('Active (can log in)')
                                ->default(true),
                        ]),
                ]),
            ]);
    }
}
