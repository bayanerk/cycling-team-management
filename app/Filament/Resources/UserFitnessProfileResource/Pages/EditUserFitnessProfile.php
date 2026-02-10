<?php

namespace App\Filament\Resources\UserFitnessProfileResource\Pages;

use App\Filament\Resources\UserFitnessProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserFitnessProfile extends EditRecord
{
    protected static string $resource = UserFitnessProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

