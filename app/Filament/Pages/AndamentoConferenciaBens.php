<?php

namespace App\Filament\Pages;

use App\Models\Conferencia;
use App\Models\Inventario;
use App\Models\Local;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AndamentoConferenciaBens extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?string $navigationLabel = 'Andamento da conferência';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventários';

    protected static ?string $title = 'Andamento da conferência';

    protected static ?string $slug = 'andamento-conferencia-bens';

    protected static ?int $navigationSort = 2;

    public function getMaxContentWidth(): \Filament\Support\Enums\Width|string|null
    {
        return \Filament\Support\Enums\Width::Full;
    }

    protected string $view = 'filament.pages.andamento-conferencia-bens';

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public static function canAccess(): bool
    {
        return Auth::check() && (Auth::user()->hasRole(['TI', 'Administrador']));
    }

    public function mount(): void
    {
        $inventarioEmAndamento = Inventario::query()
            ->where('status', 'Em andamento')
            ->latest('id')
            ->value('id');

        $inventarioPadrao = $inventarioEmAndamento ?? Inventario::query()->latest('id')->value('id');

        $this->form->fill([
            'inventarioId' => $inventarioPadrao,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('inventarioId')
                    ->label('Inventário')
                    ->options(fn () => Inventario::query()
                        ->orderByDesc('id')
                        ->pluck('titulo', 'id')
                        ->toArray())
                    ->placeholder('Selecione o inventário')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->native(false)
                    ->required(),
            ])
            ->statePath('data');
    }

    protected function inventarioSelecionadoId(): ?int
    {
        $valor = $this->data['inventarioId'] ?? null;

        return filled($valor) ? (int) $valor : null;
    }

    public function inventarioSelecionado(): ?Inventario
    {
        $inventarioId = $this->inventarioSelecionadoId();

        return $inventarioId ? Inventario::query()->find($inventarioId) : null;
    }

    /**
     * @return array{total: int, conferidos: int, pendentes: int, percentual: float}
     */
    public function resumo(): array
    {
        $inventarioId = $this->inventarioSelecionadoId();

        if (! $inventarioId) {
            return [
                'total' => 0,
                'conferidos' => 0,
                'pendentes' => 0,
                'percentual' => 0.0,
            ];
        }

        $total = Conferencia::query()
            ->where('inventario_id', $inventarioId)
            ->count();

        $conferidos = Conferencia::query()
            ->where('inventario_id', $inventarioId)
            ->whereNotNull('conferido_em')
            ->count();

        $pendentes = max($total - $conferidos, 0);
        $percentual = $total > 0 ? round(($conferidos / $total) * 100, 1) : 0.0;

        return [
            'total' => $total,
            'conferidos' => $conferidos,
            'pendentes' => $pendentes,
            'percentual' => $percentual,
        ];
    }

    /**
     * @return Collection<int, array{name: string, total: int, conferidos: int, pendentes: int, percentual: float}>
     */
    public function locaisResumo(): Collection
    {
        $inventarioId = $this->inventarioSelecionadoId();

        if (! $inventarioId) {
            return collect();
        }

        return Local::query()
            ->select('locais.id', 'locais.nome')
            ->leftJoin('conferencias', function ($join) use ($inventarioId) {
                $join->on('conferencias.local_id', '=', 'locais.id')
                    ->where('conferencias.inventario_id', $inventarioId);
            })
            ->groupBy('locais.id', 'locais.nome')
            ->orderBy('locais.nome')
            ->selectRaw('COUNT(conferencias.id) as total')
            ->selectRaw('SUM(CASE WHEN conferencias.conferido_em IS NOT NULL THEN 1 ELSE 0 END) as conferidos')
            ->get()
            ->map(function (Local $local): array {
                $total = (int) ($local->total ?? 0);
                $conferidos = (int) ($local->conferidos ?? 0);
                $pendentes = max($total - $conferidos, 0);
                $percentual = $total > 0 ? round(($conferidos / $total) * 100, 1) : 0.0;

                return [
                    'id' => $local->id,
                    'nome' => $local->nome,
                    'total' => $total,
                    'conferidos' => $conferidos,
                    'pendentes' => $pendentes,
                    'percentual' => $percentual,
                ];
            })
            ->filter(fn (array $local) => $local['total'] > 0)
            ->values();
    }

    /**
     * @return Collection<int, array{situacao: string, total: int, percentual: float}>
     */
    public function situacoesResumo(): Collection
    {
        $inventarioId = $this->inventarioSelecionadoId();

        if (! $inventarioId) {
            return collect();
        }

        return Conferencia::query()
            ->where('inventario_id', $inventarioId)
            ->whereNotNull('conferido_em')
            ->selectRaw('situacao, COUNT(*) as total')
            ->groupBy('situacao')
            ->orderByDesc('total')
            ->get()
            ->map(function ($situacao) use ($inventarioId) {
                $total = (int) $situacao->total;
                $totalGeral = Conferencia::query()
                    ->where('inventario_id', $inventarioId)
                    ->whereNotNull('conferido_em')
                    ->count();
                $percentual = $totalGeral > 0 ? round(($total / $totalGeral) * 100, 1) : 0.0;

                return [
                    'situacao' => $situacao->situacao ?? 'Sem situação',
                    'total' => $total,
                    'percentual' => $percentual,
                ];
            })
            ->values();
    }

    public function getStatusCor(string $situacao): string
    {
        return match ($situacao) {
            'Servível' => '#22c55e',
            'Inservível' => '#f97316',            
            default => '#10b981',
        };
    }
}