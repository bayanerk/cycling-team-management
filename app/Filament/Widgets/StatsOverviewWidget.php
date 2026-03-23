<?php

namespace App\Filament\Widgets;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Ride;
use App\Models\RideParticipant;
use App\Models\User;
use App\Models\UserLevel;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('All registered users')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Total Rides', Ride::count())
                ->description('All created rides')
                ->descriptionIcon('heroicon-o-map')
                ->color('success'),

            Stat::make('Total Participants', RideParticipant::count())
                ->description('All ride participations')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('warning'),

            Stat::make('Active Users with Levels', UserLevel::count())
                ->description('Users with fitness levels')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('info'),

            Stat::make('Shop products', Product::query()->where('is_active', true)->count())
                ->description('Active products in catalog')
                ->descriptionIcon('heroicon-o-shopping-bag')
                ->color('primary'),

            Stat::make('Shop orders', Order::count())
                ->description('All orders (API + admin)')
                ->descriptionIcon('heroicon-o-clipboard-document-list')
                ->color('success'),

            Stat::make('Pending reviews', ProductReview::query()->where('is_approved', false)->count())
                ->description('Awaiting moderation')
                ->descriptionIcon('heroicon-o-star')
                ->color('warning'),

            Stat::make('Active coupons', Coupon::query()->where('is_active', true)->count())
                ->description('Discount codes')
                ->descriptionIcon('heroicon-o-ticket')
                ->color('gray'),
        ];
    }
}
