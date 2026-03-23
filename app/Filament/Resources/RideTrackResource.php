<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RideTrackResource\Pages;
use App\Models\RideTrack;
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

class RideTrackResource extends Resource
{
    protected static ?string $model = RideTrack::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-map-pin';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Ride Management';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Track information')
                    ->schema([
                        Forms\Components\Select::make('ride_id')
                            ->label('Ride')
                            ->relationship('ride', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('ride_participant_id')
                            ->label('Participant')
                            ->relationship('participant', 'id', fn ($query) => $query->with('user'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name ?? "Participant #{$record->id}"),
                    ])->columns(2),

                Section::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('lat')
                            ->label('Latitude')
                            ->numeric()
                            ->required()
                            ->step(0.0000001),
                        Forms\Components\TextInput::make('lng')
                            ->label('Longitude')
                            ->numeric()
                            ->required()
                            ->step(0.0000001),
                        Forms\Components\TextInput::make('speed')
                            ->label('Speed (km/h)')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('recorded_at')
                            ->label('Recorded at')
                            ->required()
                            ->default(now()),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ride.title')
                    ->label('Ride')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('participant.user.name')
                    ->label('Participant')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lat')
                    ->label('Latitude')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lng')
                    ->label('Longitude')
                    ->sortable(),
                Tables\Columns\TextColumn::make('speed')
                    ->label('Speed (km/h)')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).' km/h' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('recorded_at')
                    ->label('Recorded at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('ride_id')
                    ->label('Ride')
                    ->relationship('ride', 'title'),
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
            ->defaultSort('recorded_at', 'desc');
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
            'index' => Pages\ListRideTracks::route('/'),
            'create' => Pages\CreateRideTrack::route('/create'),
            'edit' => Pages\EditRideTrack::route('/{record}/edit'),
        ];
    }
}
