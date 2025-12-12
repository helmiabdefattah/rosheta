<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Order;
use App\Models\Pharmacy;
use App\Models\Laboratory;
use App\Models\ClientRequest; // Using your request model as 'Orders'
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Clients', Client::count())
                ->descriptionIcon('heroicon-m-user-group'),

            Stat::make('Total Pharmacies', Pharmacy::count())
                ->descriptionIcon('heroicon-m-building-storefront'),

            Stat::make('Total Laboratories', Laboratory::count())
                ->descriptionIcon('heroicon-m-beaker'),

            Stat::make('Total Orders ', Order::count())
                ->descriptionIcon('heroicon-m-shopping-bag'),
        ];
    }
}
