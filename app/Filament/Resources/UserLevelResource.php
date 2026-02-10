<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserLevelResource\Pages;
use App\Models\UserLevel;
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

class UserLevelResource extends Resource
{
    protected static ?string $model = UserLevel::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-chart-bar';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة المستخدمين';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات المستوى')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('المستخدم')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('level_name')
                            ->label('اسم المستوى')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('مثال: Beginner, Intermediate, Advanced'),
                        Forms\Components\TextInput::make('level_number')
                            ->label('رقم المستوى')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                    ])->columns(3),
                
                Section::make('الإحصائيات')
                    ->schema([
                        Forms\Components\TextInput::make('total_distance')
                            ->label('إجمالي المسافة (كم)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0),
                        Forms\Components\TextInput::make('total_rides')
                            ->label('إجمالي الرايدات')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('total_points')
                            ->label('إجمالي النقاط')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\DateTimePicker::make('last_updated')
                            ->label('آخر تحديث')
                            ->default(now()),
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
                Tables\Columns\TextColumn::make('level_name')
                    ->label('اسم المستوى')
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
                    ->label('رقم المستوى')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_distance')
                    ->label('إجمالي المسافة (كم)')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' كم')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_rides')
                    ->label('إجمالي الرايدات')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_points')
                    ->label('إجمالي النقاط')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_updated')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level_name')
                    ->label('اسم المستوى')
                    ->options([
                        'Beginner' => 'مبتدئ',
                        'Intermediate' => 'متوسط',
                        'Advanced' => 'متقدم',
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

