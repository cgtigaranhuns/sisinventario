<?php

namespace App\Filament\Resources\Inventarios\Pages;

use App\Filament\Resources\Inventarios\InventarioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInventarios extends ListRecords
{
    protected static string $resource = InventarioResource::class;

    protected static ?string $title = 'Inventários';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo Inventário')
                ->icon('heroicon-o-plus')
                ->modalHeading('Criar Novo Inventário'),
        ];
    }
}
