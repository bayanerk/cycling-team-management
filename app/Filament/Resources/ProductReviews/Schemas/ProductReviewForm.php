<?php

namespace App\Filament\Resources\ProductReviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Review')
                    ->description('Reviews are submitted from the app. Approve to show them in the shop API.')
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'name')
                            ->disabled()
                            ->dehydrated(),
                        Select::make('user_id')
                            ->label('Customer')
                            ->relationship('user', 'name')
                            ->disabled()
                            ->dehydrated(),
                        Select::make('order_id')
                            ->label('Order')
                            ->relationship('order', 'order_number')
                            ->disabled()
                            ->dehydrated()
                            ->nullable(),
                        TextInput::make('rating')
                            ->label('Rating')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->required(),
                        Textarea::make('review')
                            ->label('Comment')
                            ->columnSpanFull(),
                        Toggle::make('is_approved')
                            ->label('Approved')
                            ->required(),
                        Toggle::make('is_visible')
                            ->label('Visible')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
