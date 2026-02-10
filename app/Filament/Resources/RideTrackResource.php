<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RideTrackResource\Pages;
use App\Models\RideTrack;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class RideTrackResource extends Resource
{
    protected static ?string $model = RideTrack::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-map-pin';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة الرايدات';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات المسار')
                    ->schema([
                        Forms\Components\Select::make('ride_id')
                            ->label('الرايد')
                            ->relationship('ride', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('ride_participant_id')
                            ->label('المشارك')
                            ->relationship('participant', 'id', fn ($query) => $query->with('user'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name ?? "Participant #{$record->id}"),
                    ])->columns(2),
                
                Section::make('الموقع الجغرافي')
                    ->schema([
                        Forms\Components\TextInput::make('lat')
                            ->label('خط العرض')
                            ->numeric()
                            ->required()
                            ->step(0.0000001),
                        Forms\Components\TextInput::make('lng')
                            ->label('خط الطول')
                            ->numeric()
                            ->required()
                            ->step(0.0000001),
                        Forms\Components\TextInput::make('speed')
                            ->label('السرعة (كم/س)')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('recorded_at')
                            ->label('وقت التسجيل')
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
                    ->label('الرايد')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('participant.user.name')
                    ->label('المشارك')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lat')
                    ->label('خط العرض')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lng')
                    ->label('خط الطول')
                    ->sortable(),
                Tables\Columns\TextColumn::make('speed')
                    ->label('السرعة (كم/س)')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' كم/س' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('recorded_at')
                    ->label('وقت التسجيل')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('ride_id')
                    ->label('الرايد')
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

