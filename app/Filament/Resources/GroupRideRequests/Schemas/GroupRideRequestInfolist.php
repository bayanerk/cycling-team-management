<?php

namespace App\Filament\Resources\GroupRideRequests\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GroupRideRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Requester')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Name'),
                        TextEntry::make('user.email')
                            ->label('Email')
                            ->placeholder('—'),
                        TextEntry::make('user.phone')
                            ->label('Phone')
                            ->placeholder('—'),
                    ])
                    ->columns(3),

                Section::make('Booking details')
                    ->schema([
                        TextEntry::make('title')
                            ->label('Ride title'),
                        TextEntry::make('group_name')
                            ->label('Group name'),
                        TextEntry::make('group_type')
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
                        TextEntry::make('location')
                            ->label('Location / destination')
                            ->columnSpanFull(),
                        TextEntry::make('scheduled_at')
                            ->label('Scheduled at')
                            ->dateTime(),
                        TextEntry::make('people_count')
                            ->label('Number of people')
                            ->suffix(' people'),
                    ])
                    ->columns(2),

                Section::make('Admin decision')
                    ->schema([
                        TextEntry::make('status')
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
                        TextEntry::make('reviewer.name')
                            ->label('Reviewed by')
                            ->placeholder('—'),
                        TextEntry::make('reviewed_at')
                            ->label('Reviewed at')
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('admin_notes')
                            ->label('Admin notes')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->label('Submitted at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Last updated')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}
