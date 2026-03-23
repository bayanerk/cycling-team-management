<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order')
                    ->schema([
                        TextEntry::make('order_number')
                            ->label('Order #'),
                        TextEntry::make('user.name')
                            ->label('Customer'),
                        TextEntry::make('user.email')
                            ->label('Email')
                            ->placeholder('—'),
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => $state ? ucfirst((string) $state) : '—'),
                        TextEntry::make('payment_method')
                            ->badge(),
                        TextEntry::make('payment_status')
                            ->badge(),
                        TextEntry::make('created_at')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Section::make('Amounts')
                    ->schema([
                        TextEntry::make('subtotal')
                            ->money('SAR'),
                        TextEntry::make('discount')
                            ->money('SAR'),
                        TextEntry::make('total')
                            ->money('SAR'),
                        TextEntry::make('coupon.code')
                            ->label('Coupon')
                            ->placeholder('—'),
                    ])
                    ->columns(4),

                Section::make('Shipping')
                    ->schema([
                        TextEntry::make('shipping_address')
                            ->label('Address')
                            ->formatStateUsing(function ($state): string {
                                if (! is_array($state)) {
                                    return (string) $state;
                                }

                                return collect($state)
                                    ->filter()
                                    ->implode(', ');
                            })
                            ->columnSpanFull(),
                        TextEntry::make('notes')
                            ->label('Customer notes')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Line items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('product_name')
                                    ->label('Product'),
                                TextEntry::make('product_price')
                                    ->money('SAR'),
                                TextEntry::make('quantity'),
                                TextEntry::make('subtotal')
                                    ->money('SAR'),
                                TextEntry::make('status')
                                    ->badge(),
                            ])
                            ->columns(5)
                            ->contained(false),
                    ]),

                Section::make('Payments')
                    ->schema([
                        RepeatableEntry::make('payments')
                            ->schema([
                                TextEntry::make('payment_number')
                                    ->label('Payment #'),
                                TextEntry::make('method')
                                    ->badge(),
                                TextEntry::make('amount')
                                    ->money('SAR'),
                                TextEntry::make('status')
                                    ->badge(),
                            ])
                            ->columns(4)
                            ->contained(false),
                    ]),

                Section::make('Cancellation')
                    ->schema([
                        TextEntry::make('cancelled_at')
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('cancelledByUser.name')
                            ->label('Cancelled by')
                            ->placeholder('—'),
                        TextEntry::make('cancellation_reason')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
