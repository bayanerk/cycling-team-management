<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
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

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-calendar';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Events';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Event information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('location')
                            ->label('Location')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('cost')
                            ->label('Cost')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->nullable(),
                    ])->columns(2),

                Section::make('Schedule')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_time')
                            ->label('Start time')
                            ->required(),
                        Forms\Components\DateTimePicker::make('end_time')
                            ->label('End time')
                            ->required(),
                    ])->columns(2),

                Section::make('Image & creator')
                    ->schema([
                        Forms\Components\FileUpload::make('image_url')
                            ->label('Event image')
                            ->image()
                            ->directory('events')
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
                Tables\Columns\TextColumn::make('cost')
                    ->label('Cost')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).' JOD' : 'Free')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Start time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('End time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created by')
                    ->sortable(),
            ])
            ->filters([
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
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
