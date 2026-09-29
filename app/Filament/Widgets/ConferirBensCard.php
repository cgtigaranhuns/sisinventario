<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ConferirBens;
use Filament\Widgets\Widget;

class ConferirBensCard extends Widget
{
    protected string $view = 'filament.widgets.conferir-bens-card';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 2;

    // Só aparece para quem tem acesso à página
    public static function canView(): bool
    {
        return ConferirBens::canAccess();
    }

    protected function getViewData(): array
    {
        return [
            'url' => ConferirBens::getUrl(),
        ];
    }
}