<?php

namespace App\Filament\Exports;

use App\Models\Bem;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class BemExporter extends Exporter
{
    protected static ?string $model = Bem::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('rp'),
            ExportColumn::make('descricao'),
            ExportColumn::make('local.nome')
                ->label('Local')
                ->state(fn (Bem $record): ?string => $record->local?->nome),
            ExportColumn::make('ultima_situacao'),
            ExportColumn::make('elemento_despesa'),
            ExportColumn::make('valor')
                ->formatStateUsing(fn ($state) => 'R$ '.number_format((float) $state, 2, ',', '.')),
            ExportColumn::make('conferidoPor.nome')
                ->label('Conferido por')
                ->state(fn (Bem $record): ?string => $record->conferidoPor?->name),
            ExportColumn::make('conferidoPor.username')
                ->label('Conferido por (SIAPE)')
                ->state(fn (Bem $record): ?string => $record->conferidoPor?->username),
            ExportColumn::make('conferido_em')
                ->formatStateUsing(fn ($state) => $state
                    ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i')
                    : null),
            ExportColumn::make('observacao'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your bem export has completed and '.Str::of('row')->counted($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Str::of('row')->counted($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
