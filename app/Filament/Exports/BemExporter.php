<?php

namespace App\Filament\Exports;

use App\Models\Bem;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

class BemExporter extends Exporter
{
    protected static ?string $model = Bem::class;

    /**
     * @var array<int, float>
     */
    protected array $xlsxColumnWidths = [];

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
            ExportColumn::make('local.uorg')
                ->label('UORG'),
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

    public function makeXlsxRow(array $values, ?Style $style = null): Row
    {
        foreach ($values as $index => $value) {
            $width = min(Str::length((string) $value) + 2, 255);
            $this->xlsxColumnWidths[$index] = max($this->xlsxColumnWidths[$index] ?? 10, $width);
        }

        return parent::makeXlsxRow($values, $style);
    }

    public function configureXlsxWriterBeforeClose(Writer $writer): Writer
    {
        foreach ($this->xlsxColumnWidths as $index => $width) {
            $writer->getCurrentSheet()->setColumnWidth($width, $index + 1);
        }

        return $writer;
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your bem export has completed and '.Str::of('row')->counted($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Str::of('row')->counted($failedRowsCount).' failed to export.';
        }

        return $body;
    }

     public function getJobConnection(): ?string
    {
        return 'sync';
    }
}
