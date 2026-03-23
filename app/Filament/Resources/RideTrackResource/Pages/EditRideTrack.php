<?php

namespace App\Filament\Resources\RideTrackResource\Pages;

use App\Filament\Resources\RideTrackResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRideTrack extends EditRecord
{
    protected static string $resource = RideTrackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
