<?php

namespace App\Filament\Resources\GroupRideRequests\Pages;

use App\Filament\Resources\GroupRideRequests\GroupRideRequestResource;
use App\Models\GroupRideRequest;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;

class ViewGroupRideRequest extends ViewRecord
{
    protected static string $resource = GroupRideRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $this->getRecord() instanceof GroupRideRequest && $this->getRecord()->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Approve booking request')
                ->modalDescription('Are you sure you want to approve this request?')
                ->action(function (): void {
                    /** @var GroupRideRequest $record */
                    $record = $this->getRecord();
                    $record->approveBy(auth()->user());
                    $record->refresh();
                })
                ->successNotificationTitle('Request approved'),
            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->getRecord() instanceof GroupRideRequest && $this->getRecord()->status === 'pending')
                ->modalHeading('Reject booking request')
                ->form([
                    Textarea::make('admin_notes')
                        ->label('Notes (optional)')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    /** @var GroupRideRequest $record */
                    $record = $this->getRecord();
                    $record->rejectBy(auth()->user(), $data['admin_notes'] ?? null);
                    $record->refresh();
                })
                ->successNotificationTitle('Request rejected'),
        ];
    }
}
