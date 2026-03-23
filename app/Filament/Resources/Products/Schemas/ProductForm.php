<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product details')
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('short_description')
                            ->label('Short description')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                        TextInput::make('price')
                            ->label('Price')
                            ->required()
                            ->numeric()
                            ->prefix('SAR'),
                        TextInput::make('compare_price')
                            ->label('Compare at price')
                            ->numeric()
                            ->prefix('SAR'),
                        TextInput::make('sku')
                            ->label('SKU')
                            ->unique(ignoreRecord: true),
                        TextInput::make('stock_quantity')
                            ->label('Stock quantity')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_in_stock')
                            ->label('In stock')
                            ->default(true),
                        Select::make('brand_id')
                            ->label('Brand')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false),
                        TextInput::make('weight')
                            ->label('Weight (kg)')
                            ->numeric()
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make('Images')
                    ->description('Shown in the mobile app and shop API. Set one image as primary.')
                    ->schema([
                        Repeater::make('images')
                            ->relationship()
                            ->schema([
                                FileUpload::make('image_path')
                                    ->label('Image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('products')
                                    ->visibility('public')
                                    ->required(),
                                Toggle::make('is_primary')
                                    ->label('Primary')
                                    ->default(false),
                                TextInput::make('order')
                                    ->label('Sort order')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                            ->addActionLabel('Add image')
                            ->collapsible()
                            ->defaultItems(0),
                    ]),
            ]);
    }
}
