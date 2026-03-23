<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RideResource\Pages;
use App\Models\Ride;
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

class RideResource extends Resource
{
    protected static ?string $model = Ride::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-map';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Ride Management';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ride information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('location')
                            ->label('Location')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('level')
                            ->label('Level')
                            ->options([
                                'Beginner' => 'Beginner',
                                'Intermediate' => 'Intermediate',
                                'Advanced' => 'Advanced',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('distance')
                            ->label('Distance (km)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->required(),
                        Forms\Components\TextInput::make('cost')
                            ->label('Cost')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->nullable(),
                    ])->columns(2),

                Section::make('Schedule')
                    ->schema([
                        Forms\Components\DateTimePicker::make('gathering_time')
                            ->label('Gathering time')
                            ->required(),
                        Forms\Components\DateTimePicker::make('start_time')
                            ->label('Start time')
                            ->required(),
                        Forms\Components\DateTimePicker::make('end_time')
                            ->label('End time')
                            ->required(),
                    ])->columns(3),

                Section::make('Geography')
                    ->schema([
                        Forms\Components\TextInput::make('start_lat')
                            ->label('Start latitude')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('start_lng')
                            ->label('Start longitude')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('end_lat')
                            ->label('End latitude')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('end_lng')
                            ->label('End longitude')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('break_location')
                            ->label('Break location')
                            ->maxLength(255)
                            ->nullable(),
                    ])->columns(2),

                Section::make('Image & creator')
                    ->schema([
                        Forms\Components\FileUpload::make('image_url')
                            ->label('Ride image')
                            ->image()
                            ->directory('rides')
                            ->nullable(),
                        Forms\Components\Select::make('created_by')
                            ->label('Created by')
                            ->relationship('creator', 'name')
                            ->required()
                            ->default(fn () => auth()->id())
                            ->searchable()
                            ->preload(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('level')
                    ->label('Level')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Beginner' => 'success',
                        'Intermediate' => 'warning',
                        'Advanced' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Beginner' => 'Beginner',
                        'Intermediate' => 'Intermediate',
                        'Advanced' => 'Advanced',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('distance')
                    ->label('Distance (km)')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' km'),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Start time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created by')
                    ->sortable(),
                Tables\Columns\TextColumn::make('participants_count')
                    ->label('Participants')
                    ->counts('participants')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->label('Level')
                    ->options([
                        'Beginner' => 'Beginner',
                        'Intermediate' => 'Intermediate',
                        'Advanced' => 'Advanced',
                    ]),
                Tables\Filters\Filter::make('start_time')
                    ->form([
                        Forms\Components\DatePicker::make('start_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('start_until')
                            ->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['start_from'],
                                fn ($query, $date) => $query->whereDate('start_time', '>=', $date),
                            )
                            ->when(
                                $data['start_until'],
                                fn ($query, $date) => $query->whereDate('start_time', '<=', $date),
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
            ]);
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
            'index' => Pages\ListRides::route('/'),
            'create' => Pages\CreateRide::route('/create'),
            'edit' => Pages\EditRide::route('/{record}/edit'),
        ];
    }
}
