<?php

namespace App\Filament\Resources\Locals\Pages;

use App\Filament\Resources\Locals\LocalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLocals extends ManageRecords
{
    protected static string $resource = LocalResource::class;
    protected static ?string $title = 'Gerenciar Locais';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo Local')
                ->modalHeading('Criar Novo Local')
                ->icon('heroicon-o-plus')
                ->modalButton('Criar Local'),
        ];
    }
}
