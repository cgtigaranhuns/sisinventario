<?php

namespace App\Http\Controllers;

use App\Models\Bem;
use App\Models\Local;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class BemRelatorioController extends Controller
{
    public function pdf(Request $request): Response
    {
        $filters = $request->validate([
            'rp' => ['nullable', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:255'],
            'local_id' => ['nullable', 'integer', 'exists:locais,id'],
            'ultima_situacao' => [
                'nullable',
                Rule::in(['Servível', 'Inservível', 'Não Localizado']),
            ],
            'elemento_despesa' => ['nullable', 'string', 'max:255'],
            'valor' => ['nullable', 'string', 'max:255'],
        ]);

        $query = Bem::query()->with('local');

        foreach (['rp', 'descricao', 'elemento_despesa', 'valor'] as $field) {
            if (filled($filters[$field] ?? null)) {
                $query->where($field, 'like', '%' . $filters[$field] . '%');
            }
        }

        if (filled($filters['local_id'] ?? null)) {
            $query->where('local_id', $filters['local_id']);
        }

        if (filled($filters['ultima_situacao'] ?? null)) {
            $query->where('ultima_situacao', $filters['ultima_situacao']);
        }

        $appliedFilters = [];
        $filterLabels = [
            'rp' => 'RP',
            'descricao' => 'Descrição',
            'ultima_situacao' => 'Última situação',
            'elemento_despesa' => 'Elemento de despesa',
            'valor' => 'Valor',
        ];

        foreach ($filterLabels as $field => $label) {
            if (filled($filters[$field] ?? null)) {
                $appliedFilters[$label] = $filters[$field];
            }
        }

        if (filled($filters['local_id'] ?? null)) {
            $appliedFilters['Local'] = Local::query()
                ->whereKey($filters['local_id'])
                ->value('nome');
        }

        $total = (float) (clone $query)->sum('valor');
        $bens = $query->orderBy('rp')->get();
        $logo = 'data:image/png;base64,' . base64_encode(File::get(public_path('img/ifpe.png')));

        $options = new Options();
        $options->setDefaultFont('DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('reports.bens-pdf', [
            'bens' => $bens,
            'total' => $total,
            'geradoEm' => now(),
            'appliedFilters' => $appliedFilters,
            'logo' => $logo,
        ])->render(), 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $pdf = $dompdf->output();
        $filename = 'relatorio-bens-' . now()->format('Ymd-His') . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
