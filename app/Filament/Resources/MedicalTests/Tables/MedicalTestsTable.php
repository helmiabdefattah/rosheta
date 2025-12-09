<?php

namespace App\Filament\Resources\MedicalTests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Filament\Resources\MedicalTestOffers\MedicalTestOfferResource;

class MedicalTestsTable
{
	public static function configure(Table $table): Table
	{
		return $table
			->columns([
				TextColumn::make('id')->label('ID')->sortable(),
				TextColumn::make('test_name_en')->label('Name (EN)')->searchable()->sortable()->weight('bold'),
				TextColumn::make('test_name_ar')->label('Name (AR)')->searchable()->sortable(),
				TextColumn::make('test_description')->label('Description')->limit(50)->toggleable(),
				TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
				TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
			])
			->filters([
				//
			])
			->recordActions([
				EditAction::make(),
				Action::make('makeOffer')
					->label('Make Offer')
					->icon('heroicon-o-tag')
					->color('primary')
					->url(fn ($record) => MedicalTestOfferResource::getUrl('create', ['medical_test_id' => $record->id])),
			])
			->toolbarActions([
				BulkActionGroup::make([
					DeleteBulkAction::make(),
				]),
			]);
	}
}


