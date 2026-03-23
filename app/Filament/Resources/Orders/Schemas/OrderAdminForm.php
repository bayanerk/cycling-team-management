<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderAdminForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Fulfillment')
                    ->description('Update order status and delivery. Totals and line items are read-only.')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                                'returned' => 'Returned',
                            ])
                            ->required(),
                        Select::make('payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                                'refunded' => 'Refunded',
                            ])
                            ->required(),
                        DateTimePicker::make('delivered_at')
                            ->label('Delivered at')
                            ->nullable(),
                        Textarea::make('notes')
                            ->label('Internal notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
