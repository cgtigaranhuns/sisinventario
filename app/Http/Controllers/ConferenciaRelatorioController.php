<?php

namespace App\Http\Controllers;

use App\Models\Conferencia;
use App\Models\Inventario;
use App\Models\Local;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class ConferenciaRelatorioController extends Controller
{
    public function pdf(Request $request, Inventario $inventario): Response
    {
        $user = auth()->user();
        $localId = $request->integer('local') ?: null;
        $auditContext = [
            'inventario_id' => $inventario->id,
            'local_id' => $localId,
            'user_id' => $user?->getAuthIdentifier(),
        ];

        if (! $user?->hasRole(['TI', 'Administrador'])) {
            Log::warning('Acesso negado à geração do relatório de conferência.', $auditContext);

            abort(403);
        }

        $startedAt = hrtime(true);
        @ini_set('memory_limit', '1024M');
        @set_time_limit(300);

        Log::info('Geração do relatório de andamento da conferência iniciada.', $auditContext);

        try {
            $nomesLocais = Local::query()->pluck('nome', 'id');

            $locais = Conferencia::query()
                ->where('inventario_id', $inventario->id)
                ->selectRaw('local_id, COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN conferido_em IS NOT NULL THEN 1 ELSE 0 END) as conferidos')
                ->groupBy('local_id')
                ->get()
                ->map(function ($linha) use ($nomesLocais): array {
                    $totalLocal = (int) $linha->total;
                    $conferidosLocal = (int) $linha->conferidos;

                    return [
                        'id' => $linha->local_id,
                        'nome' => $nomesLocais->get($linha->local_id, 'Local não identificado'),
                        'total' => $totalLocal,
                        'conferidos' => $conferidosLocal,
                        'pendentes' => max($totalLocal - $conferidosLocal, 0),
                        'percentual' => $totalLocal > 0
                            ? round(($conferidosLocal / $totalLocal) * 100, 1)
                            : 0.0,
                    ];
                })
                ->sortBy([
                    ['percentual', 'desc'],
                    ['nome', 'asc'],
                ])
                ->values();

            $total = $locais->sum('total');
            $conferidos = $locais->sum('conferidos');
            $pendentes = max($total - $conferidos, 0);
            $percentual = $total > 0 ? round(($conferidos / $total) * 100, 1) : 0.0;

            $detalhe = null;

            if ($localId) {
                $detalhe = [
                    'nome' => $nomesLocais->get($localId, 'Local não identificado'),
                    'itens' => Conferencia::query()
                        ->where('inventario_id', $inventario->id)
                        ->where('local_id', $localId)
                        ->with([
                            'bem:id,rp,descricao',
                            'conferidoPor:id,name',
                        ])
                        ->orderBy('rp_id')
                        ->get(),
                ];
            }

            Log::info('Dados do relatório carregados.', $auditContext + [
                'locais' => $locais->count(),
                'itens_detalhe' => $detalhe ? $detalhe['itens']->count() : 0,
                'memoria_mb' => round(memory_get_peak_usage(true) / 1048576),
            ]);

            $logoPath = public_path('img/ifpe.png');
            $logo = File::exists($logoPath)
                ? 'data:image/png;base64,'.base64_encode(File::get($logoPath))
                : null;

            $options = new Options;
            $options->setDefaultFont('DejaVu Sans');
            $options->setIsFontSubsettingEnabled(true);
            $options->setIsRemoteEnabled(false);

            $dompdf = new Dompdf($options);
            $html = view('reports.conferencia-pdf', [
                'inventario' => $inventario,
                'locais' => $locais,
                'detalhe' => $detalhe,
                'total' => $total,
                'conferidos' => $conferidos,
                'pendentes' => $pendentes,
                'percentual' => $percentual,
                'geradoEm' => now(),
                'logo' => $logo,
            ])->render();

            Log::info('HTML renderizado.', $auditContext + [
                'html_kb' => (int) (strlen($html) / 1024),
                'memoria_mb' => round(memory_get_peak_usage(true) / 1048576),
            ]);

            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $filename = 'relatorio-andamento-conferencia-'.$inventario->id
                .($localId ? '-local-'.$localId : '')
                .'-'.now()->format('Ymd-His').'.pdf';
            $pdf = $dompdf->output();

            Log::info('Relatório de andamento da conferência gerado com sucesso.', $auditContext + [
                'total_itens' => $total,
                'itens_conferidos' => $conferidos,
                'itens_pendentes' => $pendentes,
                'duracao_ms' => (int) ((hrtime(true) - $startedAt) / 1_000_000),
            ]);

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]);
        } catch (Throwable $exception) {
            Log::error('Falha ao gerar o relatório de andamento da conferência.', $auditContext + [
                'duracao_ms' => (int) ((hrtime(true) - $startedAt) / 1_000_000),
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }
}