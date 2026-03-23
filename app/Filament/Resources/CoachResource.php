<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoachResource\Pages;
use App\Models\Coach;
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

class CoachResource extends Resource
{
    protected static ?string $model = Coach::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-academic-cap';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Coach management';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Coach information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Textarea::make('bio')
                            ->label('Bio')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('experience_years')
                            ->label('Years of experience')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\TextInput::make('specialty')
                            ->label('Specialty')
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('rating')
                            ->label('Rating')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(5)
                            ->step(0.1)
                            ->nullable(),
                    ])->columns(2),

                Section::make('Photos & certificates')
                    ->schema([
                        Forms\Components\FileUpload::make('image_url')
                            ->label('Coach photo')
                            ->image()
                            ->directory('coaches')
                            ->nullable(),
                        Forms\Components\FileUpload::make('certificate')
                            ->label('Certificate')
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
                    ->label('Photo')
                    ->circular(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Coach name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('specialty')
                    ->label('Specialty')
                    ->searchable(),
                Tables\Columns\TextColumn::make('experience_years')
                    ->label('Experience (years)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 1) : '-'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created at')
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
