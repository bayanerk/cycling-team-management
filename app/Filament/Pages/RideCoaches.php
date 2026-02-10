<?php

namespace App\Filament\Pages;

use App\Models\Ride;
use App\Models\RideParticipant;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RideCoaches extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.ride-coaches';
    
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-user-group';
    }
    
    public static function getNavigationLabel(): string
    {
        return 'Coaches Who Joined';
    }
    
    public static function getNavigationGroup(): ?string
    {
        return 'إدارة الرايدات';
    }

    public ?int $selectedRideId = null;

    public function updatedSelectedRideId(): void
    {
        $this->resetTable();
    }

    protected function getTableQuery(): Builder
    {
        return RideParticipant::query()
            ->where('role', 'coach')
            ->when(
                $this->selectedRideId,
                fn ($query) => $query->where('ride_id', $this->selectedRideId)
            )
            ->with(['user', 'ride']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('اسم الكوتش')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('رقم الهاتف')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ride.title')
                    ->label('الرايد')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ride.start_time')
                    ->label('وقت البدء')
                    ->dateTime()
                    ->sortable(),
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
            ])
            ->emptyStateHeading('لا توجد كوتشات مسجلة')
            ->emptyStateDescription('اختر رايد من القائمة أعلاه لعرض الكوتشات المسجلين عليه')
            ->emptyStateIcon('heroicon-o-user-group');
    }

    public function getRidesOptions(): array
    {
        return Ride::query()
            ->orderBy('start_time', 'desc')
            ->get()
            ->mapWithKeys(fn ($ride) => [
                $ride->id => $ride->title . ' - ' . $ride->start_time->format('Y-m-d H:i')
            ])
            ->toArray();
    }
}
