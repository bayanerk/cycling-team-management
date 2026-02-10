<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserFitnessProfileResource\Pages;
use App\Models\UserFitnessProfile;
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

class UserFitnessProfileResource extends Resource
{
    protected static ?string $model = UserFitnessProfile::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-heart';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة المستخدمين';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات المستخدم')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('المستخدم')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])->columns(1),
                
                Section::make('القياسات البدنية')
                    ->schema([
                        Forms\Components\TextInput::make('height_cm')
                            ->label('الطول (سم)')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),
                        Forms\Components\TextInput::make('weight_kg')
                            ->label('الوزن (كجم)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->nullable(),
                    ])->columns(2),
                
                Section::make('معلومات اللياقة البدنية')
                    ->schema([
                        Forms\Components\TextInput::make('max_distance_km')
                            ->label('أقصى مسافة (كم)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->required()
                            ->helperText('المسافة القصوى التي يمكن للمستخدم قطعها'),
                        Forms\Components\DatePicker::make('last_ride_date')
                            ->label('تاريخ آخر رايد')
                            ->nullable(),
                        Forms\Components\Textarea::make('medical_notes')
                            ->label('ملاحظات طبية')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('other_sports')
                            ->label('رياضات أخرى')
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
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('height_cm')
                    ->label('الطول (سم)')
                    ->formatStateUsing(fn ($state) => $state ? $state . ' سم' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight_kg')
                    ->label('الوزن (كجم)')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' كجم' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_distance_km')
                    ->label('أقصى مسافة (كم)')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' كم' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_ride_date')
                    ->label('تاريخ آخر رايد')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('max_distance_km')
                    ->form([
                        Forms\Components\TextInput::make('min_distance')
                            ->label('أقل مسافة (كم)')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_distance')
                            ->label('أقصى مسافة (كم)')
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

