<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserFitnessProfileResource\Pages;
use App\Models\UserFitnessProfile;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class UserFitnessProfileResource extends Resource
{
    protected static ?string $model = UserFitnessProfile::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-heart';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'User management';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])->columns(1),

                Section::make('Body measurements')
                    ->schema([
                        Forms\Components\TextInput::make('height_cm')
                            ->label('Height (cm)')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),
                        Forms\Components\TextInput::make('weight_kg')
                            ->label('Weight (kg)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->nullable(),
                    ])->columns(2),

                Section::make('Fitness')
                    ->schema([
                        Forms\Components\TextInput::make('max_distance_km')
                            ->label('Max distance (km)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->required()
                            ->helperText('Maximum distance the user can ride'),
                        Forms\Components\DatePicker::make('last_ride_date')
                            ->label('Last ride date')
                            ->nullable(),
                        Forms\Components\Textarea::make('medical_notes')
                            ->label('Medical notes')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('other_sports')
                            ->label('Other sports')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('height_cm')
                    ->label('Height (cm)')
                    ->formatStateUsing(fn ($state) => $state ? $state.' cm' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight_kg')
                    ->label('Weight (kg)')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).' kg' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_distance_km')
                    ->label('Max distance (km)')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).' km' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_ride_date')
                    ->label('Last ride date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('max_distance_km')
                    ->form([
                        Forms\Components\TextInput::make('min_distance')
                            ->label('Min distance (km)')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_distance')
                            ->label('Max distance (km)')
                            ->numeric(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['min_distance'],
                                fn ($query, $distance) => $query->where('max_distance_km', '>=', $distance),
                            )
                            ->when(
                                $data['max_distance'],
                                fn ($query, $distance) => $query->where('max_distance_km', '<=', $distance),
                            );
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserFitnessProfiles::route('/'),
            'create' => Pages\CreateUserFitnessProfile::route('/create'),
            'edit' => Pages\EditUserFitnessProfile::route('/{record}/edit'),
        ];
    }
}
