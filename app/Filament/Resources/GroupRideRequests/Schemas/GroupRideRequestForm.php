<?php

namespace App\Filament\Resources\GroupRideRequests\Schemas;

use Filament\Schemas\Schema;

class GroupRideRequestForm
{
    /**
     * Requests are created from the app (API) only; no create/edit form in the panel.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([]);
    }
}
