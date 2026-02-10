<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RideResource\Pages;
use App\Models\Ride;
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

class RideResource extends Resource
{
    protected static ?string $model = Ride::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-map';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة الرايدات';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الرايد')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('العنوان')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('location')
                            ->label('الموقع')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('level')
                            ->label('المستوى')
                            ->options([
                                'Beginner' => 'مبتدئ',
                                'Intermediate' => 'متوسط',
                                'Advanced' => 'متقدم',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('distance')
                            ->label('المسافة (كم)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->required(),
                        Forms\Components\TextInput::make('cost')
                            ->label('التكلفة')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->nullable(),
                    ])->columns(2),
                
                Section::make('الأوقات')
                    ->schema([
                        Forms\Components\DateTimePicker::make('gathering_time')
                            ->label('وقت التجمع')
                            ->required(),
                        Forms\Components\DateTimePicker::make('start_time')
                            ->label('وقت البدء')
                            ->required(),
                        Forms\Components\DateTimePicker::make('end_time')
                            ->label('وقت الانتهاء')
                            ->required(),
                    ])->columns(3),
                
                Section::make('الموقع الجغرافي')
                    ->schema([
                        Forms\Components\TextInput::make('start_lat')
                            ->label('خط العرض - البداية')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('start_lng')
                            ->label('خط الطول - البداية')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('end_lat')
                            ->label('خط العرض - النهاية')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('end_lng')
                            ->label('خط الطول - النهاية')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('break_location')
                            ->label('موقع الاستراحة')
                            ->maxLength(255)
                            ->nullable(),
                    ])->columns(2),
                
                Section::make('الصورة والمنشئ')
                    ->schema([
                        Forms\Components\FileUpload::make('image_url')
                            ->label('صورة الرايد')
                            ->image()
                            ->directory('rides')
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
                Tables\Columns\TextColumn::make('level')
                    ->label('المستوى')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Beginner' => 'success',
                        'Intermediate' => 'warning',
                        'Advanced' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Beginner' => 'مبتدئ',
                        'Intermediate' => 'متوسط',
                        'Advanced' => 'متقدم',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('distance')
                    ->label('المسافة (كم)')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' كم'),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('وقت البدء')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('المنشئ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('participants_count')
                    ->label('المشاركون')
                    ->counts('participants')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->label('المستوى')
                    ->options([
                        'Beginner' => 'مبتدئ',
                        'Intermediate' => 'متوسط',
                        'Advanced' => 'متقدم',
                    ]),
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
            'index' => Pages\ListRides::route('/'),
            'create' => Pages\CreateRide::route('/create'),
            'edit' => Pages\EditRide::route('/{record}/edit'),
        ];
    }
}

