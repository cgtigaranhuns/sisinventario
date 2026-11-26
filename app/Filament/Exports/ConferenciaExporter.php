<?php

namespace App\Filament\Exports;

use App\Models\Conferencia;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

class ConferenciaExporter extends Exporter
{
    protected static ?string $model = Conferencia::class;

    /**
     * @var array<int, float>
     */
    protected array $xlsxColumnWidths = [];

    public static function getColumns(): array
    {
        return [         
            
            ExportColumn::make('bem.rp')
                ->label('RP')
                ->state(fn (Conferencia $record): ?string => $record->bem?->rp),
            ExportColumn::make('bem.descricao')
                ->label('Bem')
                ->state(fn (Conferencia $record): ?string => $record->bem?->descricao), 
            ExportColumn::make('local.nome')
                ->label('Local')
                ->state(fn (Conferencia $record): ?string => $record->local?->nome),
            ExportColumn::make('situacao')
                ->label('Situação'),
            ExportColumn::make('conferido_em')
                ->label('Conferido em')
                ->formatStateUsing(fn ($state) => $state
                    ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i')
                    : null),
            ExportColumn::make('conferidoPor.name')
                ->label('Conferido por'),
        ];
    }

    public function getXlsxHeaderCellStyle(): ?Style
    {
        return (new Style)
            ->setFontBold()
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::GREEN)
            ->setCellAlignment('center')
            ->setCellVerticalAlignment('center')
            ->setShouldWrapText();
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
        $body = 'Your conferencia export has completed and ' . Str::of('row')->counted($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Str::of('row')->counted($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getJobConnection(): ?string
    {
        return 'sync';
    }
}
