<?php

namespace App\Filament\Pages;

use App\Models\Bem;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Opções de situação do bem — ajuste livremente para o vocabulário
 * usado na sua instituição (ex.: nomenclatura do Decreto 99.658/90).
 */
class ConferenciaBens extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;
    protected static ?string $navigationLabel = 'Conferência de Bens';
    protected static ?string $title = 'Conferência de Bens';
    protected static ?string $slug = 'conferencia-de-bens';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.conferencia-bens';

    protected const SITUACOES = [
        'Servível' => 'Servível',
        'Inservível' => 'Inservível',
        'Ocioso' => 'Ocioso',
        'Recuperável' => 'Recuperável',
        'Antieconômico' => 'Antieconômico',
        'Irrecuperável' => 'Irrecuperável',
        'Não localizado' => 'Não localizado',
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(Bem::query())
            // Clicar na linha NÃO abre outra página — tudo é editado
            // ali mesmo, sem sair da lista.
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('rp')
                    ->label('RP')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                   // ->wrap()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->descricao),

                Tables\Columns\SelectColumn::make('local')
                    ->label('Local')
                    ->options(fn () => DB::table('locais')
                        ->orderBy('nome')
                        ->pluck('nome', 'nome')
                        ->toArray())
                    ->searchable()
                    ->selectablePlaceholder(false),

                Tables\Columns\SelectColumn::make('situacao')
                    ->label('Situação')
                    ->options(self::SITUACOES)
                    ->selectablePlaceholder(false),

                Tables\Columns\IconColumn::make('conferido')
                    ->label('Conferido')
                    ->boolean()
                    ->getStateUsing(fn ($record) => filled($record->conferido_em))
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(query: fn ($query, string $direction) => $query
                        ->orderBy('conferido_em', $direction)),

                Tables\Columns\TextColumn::make('conferido_em')
                    ->label('Conferido em')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('conferidoPor.name')
                    ->label('Conferido por')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_conferencia')
                    ->label('Status')
                    ->options([
                        'pendente' => 'Pendente',
                        'conferido' => 'Conferido',
                    ])
                    ->query(fn ($query, array $data) => match ($data['value'] ?? null) {
                        'pendente' => $query->whereNull('conferido_em'),
                        'conferido' => $query->whereNotNull('conferido_em'),
                        default => $query,
                    }),

                Tables\Filters\SelectFilter::make('local')
                    ->options(fn () => Bem::query()
                        ->whereNotNull('local')
                        ->distinct()
                        ->orderBy('local')
                        ->pluck('local', 'local')
                        ->toArray())
                    ->searchable(),

                Tables\Filters\SelectFilter::make('situacao')
                    ->options(self::SITUACOES),
            ])
            ->actions([
                Action::make('marcarConferido')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => blank($record->conferido_em))
                    ->action(function ($record) {
                        $record->update([
                            'conferido_em' => now(),
                            'conferido_por_id' => Auth::id(),
                        ]);

                        Notification::make()
                            ->title("RP {$record->rp} confirmado")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkAction::make('confirmarSelecionados')
                    ->label('Confirmar selecionados (sem alterar dados)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $records->each->update([
                            'conferido_em' => now(),
                            'conferido_por_id' => Auth::id(),
                        ]);
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->defaultSort('rp')
            ->paginated([25, 50, 100, 'all'])
            ->defaultPaginationPageOption(50)
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession()
            ->poll(null);
    }
}