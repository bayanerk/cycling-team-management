<?php

namespace App\Filament\Resources\RideParticipantResource\Pages;

use App\Filament\Resources\RideParticipantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRideParticipants extends ListRecords
{
    protected static string $resource = RideParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
