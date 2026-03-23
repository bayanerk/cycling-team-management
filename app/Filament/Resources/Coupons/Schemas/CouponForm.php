<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(64)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                        Select::make('type')
                            ->options([
                                'percentage' => 'Percentage',
                                'fixed' => 'Fixed amount',
                            ])
                            ->default('percentage')
                            ->required(),
                        TextInput::make('value')
                            ->label('Value')
                            ->required()
                            ->numeric()
                            ->helperText('Percentage (e.g. 10) or fixed amount in SAR.'),
                        TextInput::make('minimum_amount')
                            ->label('Minimum order amount')
                            ->numeric()
                            ->prefix('SAR'),
                        TextInput::make('maximum_discount')
                            ->label('Maximum discount cap')
                            ->numeric()
                            ->prefix('SAR'),
                        TextInput::make('usage_limit')
                            ->label('Total usage limit')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('usage_count')
                            ->label('Times used')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated()
                            ->visibleOn('edit'),
                        TextInput::make('user_limit')
                            ->label('Max uses per user')
                            ->numeric()
                            ->nullable(),
                        DatePicker::make('starts_at')
                            ->label('Starts at'),
                        DatePicker::make('expires_at')
                            ->label('Expires at'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
