<?php

namespace App\Filament\Resources\Permissions\Pages;

use App\Filament\Resources\Permissions\PermissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePermissions extends ManageRecords
{
    protected static string $resource = PermissionResource::class;

    protected static ?string $title = 'Permissões';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Criar Permissão')
                ->icon('heroicon-s-plus')
                ->modalHeading('Criar Permissão'),
        ];
    }
}
