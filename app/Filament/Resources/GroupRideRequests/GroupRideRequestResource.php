<?php

namespace App\Filament\Resources\GroupRideRequests;

use App\Filament\Resources\GroupRideRequests\Pages\ListGroupRideRequests;
use App\Filament\Resources\GroupRideRequests\Pages\ViewGroupRideRequest;
use App\Filament\Resources\GroupRideRequests\Schemas\GroupRideRequestForm;
use App\Filament\Resources\GroupRideRequests\Schemas\GroupRideRequestInfolist;
use App\Filament\Resources\GroupRideRequests\Tables\GroupRideRequestsTable;
use App\Models\GroupRideRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GroupRideRequestResource extends Resource
{
    protected static ?string $model = GroupRideRequest::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Ride Management';
    }

    public static function getNavigationLabel(): string
    {
        return 'Group ride requests';
    }

    public static function getModelLabel(): string
    {
        return 'Group ride request';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Group ride requests';
    }

    public static function getNavigationSort(): ?int
    {
        return 15;
    }

    public static function form(Schema $schema): Schema
    {
        return GroupRideRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GroupRideRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GroupRideRequestsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'reviewer']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGroupRideRequests::route('/'),
            'view' => ViewGroupRideRequest::route('/{record}'),
        ];
    }
}
