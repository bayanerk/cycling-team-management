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
        return 'Coaches who joined';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Ride Management';
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
                    ->label('Coach name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ride.title')
                    ->label('Ride')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ride.start_time')
                    ->label('Start time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
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
                        'joined' => 'Joined',
                        'cancelled' => 'Cancelled',
                        'excused' => 'Excused',
                        'completed' => 'Completed',
                        'no_show' => 'No show',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('joined_at')
                    ->label('Joined at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'joined' => 'Joined',
                        'cancelled' => 'Cancelled',
                        'excused' => 'Excused',
                        'completed' => 'Completed',
                        'no_show' => 'No show',
                    ]),
            ])
            ->emptyStateHeading('No coaches registered')
            ->emptyStateDescription('Select a ride from the list above to see coaches registered for it.')
            ->emptyStateIcon('heroicon-o-user-group');
    }

    public function getRidesOptions(): array
    {
        return Ride::query()
            ->orderBy('start_time', 'desc')
            ->get()
            ->mapWithKeys(fn ($ride) => [
                $ride->id => $ride->title.' - '.$ride->start_time->format('Y-m-d H:i'),
            ])
            ->toArray();
    }
}
