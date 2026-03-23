<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemoryResource\Pages;
use App\Models\Memory;
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

class MemoryResource extends Resource
{
    protected static ?string $model = Memory::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-photo';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Memories';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Memory details')
                    ->schema([
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Image')
                            ->image()
                            ->required()
                            ->directory('memories')
                            ->maxSize(5120),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('photo_date')
                            ->label('Photo date')
                            ->required()
                            ->default(now()),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active memories are shown in the app'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->circular(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('photo_date')
                    ->label('Photo date')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\Filter::make('photo_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn ($query, $date) => $query->whereDate('photo_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn ($query, $date) => $query->whereDate('photo_date', '<=', $date),
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
            ->defaultSort('photo_date', 'desc');
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
            'index' => Pages\ListMemories::route('/'),
            'create' => Pages\CreateMemory::route('/create'),
            'edit' => Pages\EditMemory::route('/{record}/edit'),
        ];
    }
}
