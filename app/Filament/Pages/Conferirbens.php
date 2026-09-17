<?php

namespace App\Filament\Pages;

use App\Models\Bem;
use App\Models\Conferencia;
use App\Models\Inventario;
use App\Models\Local;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Marcelorodrigo\FilamentBarcodeScannerField\Forms\Components\BarcodeInput;

/**
 * Tela de conferência: cada usuário só vê os bens do local pelo qual
 * é responsável (User::local_id), dentro do inventário "Em
 * andamento" escolhido no filtro do topo. Cada conferência é
 * gravada em `conferencias` (uma linha por bem, por inventário,
 * ligada pela coluna rp_id) — o cadastro do Bem em si nunca é
 * alterado por essa tela.
 */
class ConferirBens extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Conferência';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventários';

    protected static ?string $title = 'Conferência de bens';

    protected static ?string $slug = 'conferencia-bens';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.conferir-bens';

    /**
     * Estado do formulário de seleção do inventário.
     *
     * @var array<string, mixed>
     */
    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'inventarioId' => Inventario::query()
                ->where('status', 'Em andamento')
                ->value('id'),
        ]);
    }

    /**
     * Recria a tabela quando o inventário selecionado muda.
     */
    public function updatedDataInventarioId(): void
    {
        $this->resetTable();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('inventarioId')
                    ->label('Inventário em conferência')
                    ->options(fn () => Inventario::query()
                        ->where('status', 'Em andamento')
                        ->orderBy('titulo')
                        ->pluck('titulo', 'id')
                        ->toArray())
                    ->placeholder('Selecione um inventário')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->helperText('Os bens serão carregados conforme o inventário selecionado.')
                    ->native(false),
            ])
            ->statePath('data');
    }

    protected const SITUACOES = [
        'Servível' => 'Servível',
        'Inservível' => 'Inservível',
        'Ocioso' => 'Ocioso',
        'Recuperável' => 'Recuperável',
        'Antieconômico' => 'Antieconômico',
        'Irrecuperável' => 'Irrecuperável',
        'Não localizado' => 'Não localizado',
    ];

    /**
     * IDs dos locais pelos quais o usuário logado é responsável.
     * Hoje é sempre um único local (users.local_id), mas fica em
     * array para o dia em que isso virar muitos-para-muitos.
     */
    protected function locaisDoUsuarioIds(): array
    {
        return array_values(array_filter(
            Arr::flatten(Auth::user()->local_id ?? []),
            fn ($id) => filled($id),
        ));
    }

    /**
     * Inventário escolhido no <select> do topo da página (ou null se
     * ainda não escolhido / não houver nenhum em andamento).
     */
    protected function inventarioSelecionadoId(): ?int
    {
        $valor = $this->data['inventarioId'] ?? null;

        return filled($valor) ? (int) $valor : null;
    }

    protected function conferenciaDoRegistro(Bem $bem, ?int $inventarioId): ?Conferencia
    {
        if (! $inventarioId) {
            return null;
        }

        return $bem->conferencias->firstWhere('inventario_id', $inventarioId);
    }

    protected function salvarConferencia(Bem $bem, int $inventarioId, array $dados, bool $marcarComoConferido = true): Conferencia
    {
        return Conferencia::updateOrCreate(
            ['inventario_id' => $inventarioId, 'rp_id' => $bem->id],
            array_merge($dados, [
                'conferido_em' => $marcarComoConferido ? now() : null,
                'conferido_por_id' => $marcarComoConferido ? Auth::id() : null,
            ])
        );
    }

    public function table(Table $table): Table
    {
        $locaisIds = $this->locaisDoUsuarioIds();

        return $table
            ->query(function () use ($locaisIds) {
                $inventarioId = $this->inventarioSelecionadoId();

                $query = Bem::query()->whereHas('conferencias', function ($query) use ($inventarioId, $locaisIds) {
                    $query
                        ->where('inventario_id', $inventarioId)
                        ->whereIn('local_id', $locaisIds);
                });

                if ($inventarioId) {
                    $query->with(['conferencias' => fn ($q) => $q->where('inventario_id', $inventarioId)]);
                } else {
                    // Sem inventário selecionado ainda: não mostra nenhum bem,
                    // só a mensagem pedindo para escolher um.
                    $query->whereRaw('1 = 0');
                }

                return $query;
            })
            ->recordUrl(null)
            ->emptyStateHeading(
                $this->inventarioSelecionadoId()
                    ? 'Nenhum bem encontrado no seu local'
                    : 'Selecione um inventário em andamento'
            )
            ->emptyStateDescription(
                $this->inventarioSelecionadoId()
                    ? null
                    : 'Escolha, no campo acima, qual inventário você vai conferir.'
            )
            ->columns([
                TextColumn::make('rp')
                    ->label('RP')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->wrap()
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->descricao),

                SelectColumn::make('conferencia_local_id')
                    ->label('Local')
                    ->options(fn () => Local::query()->orderBy('nome')->pluck('nome', 'id')->toArray())
                    ->selectablePlaceholder(false)
                    ->getStateUsing(function (Bem $record) {
                        $conferencia = $this->conferenciaDoRegistro($record, $this->inventarioSelecionadoId());

                        return $conferencia?->local_id ?? $record->local_id;
                    })
                    ->updateStateUsing(function (Bem $record, $state) {
                        $inventarioId = $this->inventarioSelecionadoId();

                        if (! $inventarioId) {
                            return;
                        }

                        $conferenciaAtual = $this->conferenciaDoRegistro($record, $inventarioId);
                        $estavaConferido = filled($conferenciaAtual?->conferido_em);

                        $this->salvarConferencia(
                            $record,
                            $inventarioId,
                            [
                                'local_id' => $state,
                                'situacao' => $conferenciaAtual->situacao ?? $record->situacao,
                            ],
                            ! $estavaConferido,
                        );

                        Notification::make()
                            ->title($estavaConferido ? 'Local atualizado — item voltou para pendente' : 'Local atualizado')
                            ->success()
                            ->send();
                    }),

                SelectColumn::make('conferencia_situacao')
                    ->label('Situação')
                    ->options(self::SITUACOES)
                    ->selectablePlaceholder(false)
                    ->getStateUsing(function (Bem $record) {
                        $conferencia = $this->conferenciaDoRegistro($record, $this->inventarioSelecionadoId());

                        return $conferencia?->situacao ?? $record->situacao;
                    })
                    ->updateStateUsing(function (Bem $record, $state) {
                        $inventarioId = $this->inventarioSelecionadoId();

                        if (! $inventarioId) {
                            return;
                        }

                        $conferenciaAtual = $this->conferenciaDoRegistro($record, $inventarioId);
                        $estavaConferido = filled($conferenciaAtual?->conferido_em);

                        $this->salvarConferencia(
                            $record,
                            $inventarioId,
                            [
                                'local_id' => $conferenciaAtual->local_id ?? $record->local_id,
                                'situacao' => $state,
                            ],
                            ! $estavaConferido,
                        );

                        Notification::make()
                            ->title($estavaConferido ? 'Situação atualizada — item voltou para pendente' : 'Situação atualizada')
                            ->success()
                            ->send();
                    }),

                TextColumn::make('status_conferencia')
                    ->label('Status')
                    ->state(function (Bem $record) {
                        $conferencia = $this->conferenciaDoRegistro($record, $this->inventarioSelecionadoId());

                        return filled($conferencia?->conferido_em) ? 'Conferido' : 'Pendente';
                    })
                    ->badge()
                    ->color(function (Bem $record) {
                        $conferencia = $this->conferenciaDoRegistro($record, $this->inventarioSelecionadoId());

                        return filled($conferencia?->conferido_em) ? 'success' : 'warning';
                    })
                    ->sortable(false),

                TextColumn::make('conferido_em')
                    ->label('Conferido em')
                    ->state(fn (Bem $record) => $this->conferenciaDoRegistro($record, $this->inventarioSelecionadoId())?->conferido_em)
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('conferido_por_id')
                    ->label('Conferido por')
                    ->state(fn (Bem $record) => $this->conferenciaDoRegistro($record, $this->inventarioSelecionadoId())?->conferidoPor?->name ?? $this->conferenciaDoRegistro($record, $this->inventarioSelecionadoId())?->conferido_por_id)
                   // ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status_conferencia')
                    ->label('Status')
                    ->options([
                        'pendente' => 'Pendente',
                        'conferido' => 'Conferido',
                    ])
                    ->query(function ($query, array $data) {
                        $inventarioId = $this->inventarioSelecionadoId();

                        if (! $inventarioId || blank($data['value'] ?? null)) {
                            return $query;
                        }

                        $rpIdsConferidos = Conferencia::query()
                            ->where('inventario_id', $inventarioId)
                            ->whereNotNull('conferido_em')
                            ->pluck('rp_id');

                        return $data['value'] === 'conferido'
                            ? $query->whereIn('id', $rpIdsConferidos)
                            : $query->whereNotIn('id', $rpIdsConferidos);
                    }),
            ])
            ->recordActions([
                Action::make('marcarConferido')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Bem $record) => $this->inventarioSelecionadoId()
                        && blank($this->conferenciaDoRegistro($record, $this->inventarioSelecionadoId())?->conferido_em))
                    ->action(function (Bem $record) {
                        $inventarioId = $this->inventarioSelecionadoId();
                        $conferenciaAtual = $this->conferenciaDoRegistro($record, $inventarioId);

                        $this->salvarConferencia($record, $inventarioId, [
                            'local_id' => $conferenciaAtual->local_id ?? $record->local_id,
                            'situacao' => $conferenciaAtual->situacao ?? $record->situacao,
                        ]);

                        Notification::make()
                            ->title("RP {$record->rp} confirmado")
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('confirmarSelecionados')
                    ->label('Confirmar selecionados (sem alterar dados)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $inventarioId = $this->inventarioSelecionadoId();

                        if (! $inventarioId) {
                            return;
                        }

                        foreach ($records as $record) {
                            $conferenciaAtual = $this->conferenciaDoRegistro($record, $inventarioId);

                            $this->salvarConferencia($record, $inventarioId, [
                                'local_id' => $conferenciaAtual->local_id ?? $record->local_id,
                                'situacao' => $conferenciaAtual->situacao ?? $record->situacao,
                            ]);
                        }
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->headerActions([
                Action::make('escanear')
                    ->label('Escanear código de barras')
                    ->icon('heroicon-o-qr-code')
                    ->color('gray')
                    ->form([
                        BarcodeInput::make('rp')
                            ->label('RP do bem')
                            ->placeholder('Aponte a câmera para o código do bem...')
                            ->required()
                            ->rules(['exists:bens,rp'])
                            ->validationMessages([
                                'exists' => 'Não encontrei nenhum bem com esse RP.',
                            ]),
                    ])
                    ->action(function (array $data) {
                        // Filtra a tabela pelo RP lido — o conferente cai
                        // direto na linha do item, sem precisar digitar nada.
                        $this->tableSearch = (string) $data['rp'];
                        $this->resetPage();
 
                        Notification::make()
                            ->title("RP {$data['rp']} localizado")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('rp')
            ->paginated([25, 50, 100, 'all'])
            ->defaultPaginationPageOption(50)
            ->persistSearchInSession()
            ->persistSortInSession()
            ->deferFilters(false)
            ->poll(null);
    }
}
