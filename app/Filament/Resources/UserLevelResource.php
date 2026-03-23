<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserLevelResource\Pages;
use App\Models\UserLevel;
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

class UserLevelResource extends Resource
{
    protected static ?string $model = UserLevel::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-chart-bar';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'User management';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Level information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('level_name')
                            ->label('Level name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Beginner, Intermediate, Advanced'),
                        Forms\Components\TextInput::make('level_number')
                            ->label('Level number')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                    ])->columns(3),

                Section::make('Statistics')
                    ->schema([
                        Forms\Components\TextInput::make('total_distance')
                            ->label('Total distance (km)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0),
                        Forms\Components\TextInput::make('total_rides')
                            ->label('Total rides')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('total_points')
                            ->label('Total points')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\DateTimePicker::make('last_updated')
                            ->label('Last updated')
                            ->default(now()),
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
                Tables\Columns\TextColumn::make('level_name')
                    ->label('Level name')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'Beginner') => 'success',
                        str_contains($state, 'Intermediate') => 'warning',
                        str_contains($state, 'Advanced') => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('level_number')
                    ->label('Level #')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_distance')
                    ->label('Total distance (km)')
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' km')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_rides')
                    ->label('Total rides')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_points')
                    ->label('Total points')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_updated')
                    ->label('Last updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level_name')
                    ->label('Level name')
                    ->options([
                        'Beginner' => 'Beginner',
                        'Intermediate' => 'Intermediate',
                        'Advanced' => 'Advanced',
                    ]),
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
            ->defaultSort('last_updated', 'desc');
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
            'index' => Pages\ListUserLevels::route('/'),
            'create' => Pages\CreateUserLevel::route('/create'),
            'edit' => Pages\EditUserLevel::route('/{record}/edit'),
        ];
    }
}
