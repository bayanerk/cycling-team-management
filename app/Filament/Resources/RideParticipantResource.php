<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RideParticipantResource\Pages;
use App\Models\RideParticipant;
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

class RideParticipantResource extends Resource
{
    protected static ?string $model = RideParticipant::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-user-group';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة الرايدات';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات المشاركة')
                    ->schema([
                        Forms\Components\Select::make('ride_id')
                            ->label('الرايد')
                            ->relationship('ride', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('user_id')
                            ->label('المستخدم')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('role')
                            ->label('الدور')
                            ->options([
                                'rider' => 'راكب',
                                'coach' => 'مدرب',
                            ])
                            ->default('rider')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'joined' => 'منضم',
                                'cancelled' => 'ملغي',
                                'excused' => 'معذور',
                                'completed' => 'مكتمل',
                                'no_show' => 'لم يحضر',
                            ])
                            ->required()
                            ->default('joined'),
                    ])->columns(2),
                
                Section::make('تفاصيل الأداء')
                    ->schema([
                        Forms\Components\TextInput::make('distance_km')
                            ->label('المسافة (كم)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->nullable(),
                        Forms\Components\TextInput::make('avg_speed_kmh')
                            ->label('متوسط السرعة (كم/س)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->nullable(),
                        Forms\Components\TextInput::make('calories_burned')
                            ->label('السعرات الحرارية')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),
                        Forms\Components\TextInput::make('points_earned')
                            ->label('النقاط المكتسبة')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),
                    ])->columns(2),
                
                Section::make('الأوقات')
                    ->schema([
                        Forms\Components\DateTimePicker::make('joined_at')
                            ->label('وقت الانضمام')
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('cancelled_at')
                            ->label('وقت الإلغاء')
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('excused_at')
                            ->label('وقت العذر')
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('وقت الإكمال')
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('checked_at')
                            ->label('وقت التحقق')
                            ->nullable(),
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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('الدور')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'rider' => 'success',
                        'coach' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'rider' => 'راكب',
                        'coach' => 'مدرب',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
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
                        'joined' => 'منضم',
                        'cancelled' => 'ملغي',
                        'excused' => 'معذور',
                        'completed' => 'مكتمل',
                        'no_show' => 'لم يحضر',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('distance_km')
                    ->label('المسافة (كم)')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' كم' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('points_earned')
                    ->label('النقاط')
                    ->sortable(),
                Tables\Columns\TextColumn::make('joined_at')
                    ->label('وقت الانضمام')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'joined' => 'منضم',
                        'cancelled' => 'ملغي',
                        'excused' => 'معذور',
                        'completed' => 'مكتمل',
                        'no_show' => 'لم يحضر',
                    ]),
                Tables\Filters\SelectFilter::make('role')
                    ->label('الدور')
                    ->options([
                        'rider' => 'راكب',
                        'coach' => 'مدرب',
                    ]),
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

