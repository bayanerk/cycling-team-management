<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoachResource\Pages;
use App\Models\Coach;
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

class CoachResource extends Resource
{
    protected static ?string $model = Coach::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-academic-cap';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة المدربين';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات المدرب')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('المستخدم')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Textarea::make('bio')
                            ->label('السيرة الذاتية')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('experience_years')
                            ->label('سنوات الخبرة')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\TextInput::make('specialty')
                            ->label('التخصص')
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('rating')
                            ->label('التقييم')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(5)
                            ->step(0.1)
                            ->nullable(),
                    ])->columns(2),
                
                Section::make('الصور والشهادات')
                    ->schema([
                        Forms\Components\FileUpload::make('image_url')
                            ->label('صورة المدرب')
                            ->image()
                            ->directory('coaches')
                            ->nullable(),
                        Forms\Components\FileUpload::make('certificate')
                            ->label('الشهادة')
                            ->directory('certificates')
                            ->nullable(),
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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('اسم المدرب')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('specialty')
                    ->label('التخصص')
                    ->searchable(),
                Tables\Columns\TextColumn::make('experience_years')
                    ->label('سنوات الخبرة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rating')
                    ->label('التقييم')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 1) : '-'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            'index' => Pages\ListCoaches::route('/'),
            'create' => Pages\CreateCoach::route('/create'),
            'edit' => Pages\EditCoach::route('/{record}/edit'),
        ];
    }
}

