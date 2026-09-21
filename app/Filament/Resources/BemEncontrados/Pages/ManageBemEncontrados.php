<?php

namespace App\Filament\Resources\BemEncontrados\Pages;

use App\Filament\Resources\BemEncontrados\BemEncontradoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageBemEncontrados extends ManageRecords
{
    protected static string $resource = BemEncontradoResource::class;

    protected static ?string $title = 'Bens encontrados';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo bem encontrado')
                ->icon('heroicon-o-plus')
                ->modalHeading('Adicionar bem encontrado'),
        ];
    }
}
