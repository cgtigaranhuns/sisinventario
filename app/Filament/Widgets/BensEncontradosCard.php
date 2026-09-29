<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\BemEncontrados\BemEncontradoResource;
use Filament\Widgets\Widget;

class BensEncontradosCard extends Widget
{
    protected string $view = 'filament.widgets.bens-encontrados-card';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 2;

    public static function canView(): bool
    {
        return BemEncontradoResource::canViewAny();
    }

    protected function getViewData(): array
    {
        return [
            'url' => BemEncontradoResource::getUrl('index'),
        ];
    }
}