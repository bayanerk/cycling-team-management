<?php

namespace App\Filament\Resources\GroupRideRequests\Pages;

use App\Filament\Resources\GroupRideRequests\GroupRideRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListGroupRideRequests extends ListRecords
{
    protected static string $resource = GroupRideRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
