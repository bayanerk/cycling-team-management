<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
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

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-calendar';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة الفعاليات';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الفعالية')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('العنوان')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('location')
                            ->label('الموقع')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('cost')
                            ->label('التكلفة')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->nullable(),
                    ])->columns(2),
                
                Section::make('الأوقات')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_time')
                            ->label('وقت البدء')
                            ->required(),
                        Forms\Components\DateTimePicker::make('end_time')
                            ->label('وقت الانتهاء')
                            ->required(),
                    ])->columns(2),
                
                Section::make('الصورة والمنشئ')
                    ->schema([
                        Forms\Components\FileUpload::make('image_url')
                            ->label('صورة الفعالية')
                            ->image()
                            ->directory('events')
                            ->nullable(),
                        Forms\Components\Select::make('created_by')
                            ->label('المنشئ')
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
                    ->label('الصورة')
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('الموقع')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cost')
                    ->label('التكلفة')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' د.أ' : 'مجاني')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('وقت البدء')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('وقت الانتهاء')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('المنشئ')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('start_time')
                    ->form([
                        Forms\Components\DatePicker::make('start_from')
                            ->label('من تاريخ'),
                        Forms\Components\DatePicker::make('start_until')
                            ->label('إلى تاريخ'),
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

