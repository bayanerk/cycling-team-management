<?php

namespace App\Filament\Resources\GroupRideRequests\Tables;

use App\Models\GroupRideRequest;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GroupRideRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Ride title')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('group_name')
                    ->label('Group name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('group_type')
                    ->label('Group type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'friends' => 'Friends',
                        'scouts' => 'Scouts',
                        'institute' => 'Institute',
                        'association' => 'Association',
                        'organization' => 'Organization',
                        'other' => 'Other',
                        default => (string) $state,
                    }),
                TextColumn::make('scheduled_at')
                    ->label('Scheduled at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('people_count')
                    ->label('People')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        default => (string) $state,
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('reviewer.name')
                    ->label('Reviewed by')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Submitted at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label('View'),
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (GroupRideRequest $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Approve booking request')
                    ->modalDescription('Are you sure you want to approve this request?')
                    ->action(function (GroupRideRequest $record): void {
                        $record->approveBy(auth()->user());
                    })
                    ->successNotificationTitle('Request approved'),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (GroupRideRequest $record): bool => $record->status === 'pending')
                    ->modalHeading('Reject booking request')
                    ->form([
                        Textarea::make('admin_notes')
                            ->label('Notes (optional)')
                            ->rows(3),
                    ])
                    ->action(function (array $data, GroupRideRequest $record): void {
                        $record->rejectBy(auth()->user(), $data['admin_notes'] ?? null);
                    })
                    ->successNotificationTitle('Request rejected'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
