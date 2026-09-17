<?php

namespace App\Filament\Resources\Bems\Pages;

use App\Filament\Resources\Bems\BemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageBems extends ManageRecords
{
    protected static string $resource = BemResource::class;

    protected static ?string $title = 'Bens';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo Bem')
                ->icon('heroicon-o-plus')
                ->modalHeading('Criar Novo Bem'),
        ];
    }
}
