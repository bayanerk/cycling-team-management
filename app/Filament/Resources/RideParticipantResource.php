<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RideParticipantResource\Pages;
use App\Models\RideParticipant;
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

class RideParticipantResource extends Resource
{
    protected static ?string $model = RideParticipant::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-user-group';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Ride Management';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Participation')
                    ->schema([
                        Forms\Components\Select::make('ride_id')
                            ->label('Ride')
                            ->relationship('ride', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('role')
                            ->label('Role')
                            ->options([
                                'rider' => 'Rider',
                                'coach' => 'Coach',
                            ])
                            ->default('rider')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'joined' => 'Joined',
                                'cancelled' => 'Cancelled',
                                'excused' => 'Excused',
                                'completed' => 'Completed',
                                'no_show' => 'No show',
                            ])
                            ->required()
                            ->default('joined'),
                    ])->columns(2),

                Section::make('Performance')
                    ->schema([
                        Forms\Components\TextInput::make('distance_km')
                            ->label('Distance (km)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->nullable(),
                        Forms\Components\TextInput::make('avg_speed_kmh')
                            ->label('Avg. speed (km/h)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->nullable(),
                        Forms\Components\TextInput::make('calories_burned')
                            ->label('Calories burned')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),
                        Forms\Components\TextInput::make('points_earned')
                            ->label('Points earned')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),
                    ])->columns(2),

                Section::make('Timestamps')
                    ->schema([
                        Forms\Components\DateTimePicker::make('joined_at')
                            ->label('Joined at')
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('cancelled_at')
                            ->label('Cancelled at')
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('excused_at')
                            ->label('Excused at')
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Completed at')
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('checked_at')
                            ->label('Checked at')
                            ->nullable(),
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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'rider' => 'success',
                        'coach' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'rider' => 'Rider',
                        'coach' => 'Coach',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'joined' => 'success',
                        'cancelled' => 'warning',
                        'excused' => 'info',
                        'completed' => 'primary',
                        'no_show' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'joined' => 'Joined',
                        'cancelled' => 'Cancelled',
                        'excused' => 'Excused',
                        'completed' => 'Completed',
                        'no_show' => 'No show',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('distance_km')
                    ->label('Distance (km)')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).' km' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('points_earned')
                    ->label('Points')
                    ->sortable(),
                Tables\Columns\TextColumn::make('joined_at')
                    ->label('Joined at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'joined' => 'Joined',
                        'cancelled' => 'Cancelled',
                        'excused' => 'Excused',
                        'completed' => 'Completed',
                        'no_show' => 'No show',
                    ]),
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'rider' => 'Rider',
                        'coach' => 'Coach',
                    ]),
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
            'index' => Pages\ListRideParticipants::route('/'),
            'create' => Pages\CreateRideParticipant::route('/create'),
            'edit' => Pages\EditRideParticipant::route('/{record}/edit'),
        ];
    }
}
