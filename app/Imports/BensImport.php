<?php

namespace App\Imports;

use App\Models\Bem;
use App\Models\Local;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Filament\Notifications\Notification;

/**
 * Importa a planilha de bens para a tabela `bens`.
 *
 * ORDEM DAS COLUNAS ESPERADA (linha 1 = cabeçalho, é ignorada):
 *
 *   A) RP                 (obrigatório — usado para não duplicar bens já importados)
 *   B) Descrição          (obrigatório)
 *   C) Local              (nome do local; é criado automaticamente se ainda não existir)
 *   D) Situação           (obrigatório)
 *   E) Elemento de Despesa (opcional)
 *   F) Valor              (opcional — aceita "1234,56" ou "1234.56")
 *   G) Observação         (opcional)
 *
 * A importação é processada pelo job de aplicação, sem enfileirar a
 * própria classe do importador. Os contadores de progresso ficam no Cache,
 * porque o processamento pode ser feito em chunks.
 */
class BensImport implements SkipsOnFailure, ToModel, WithBatchInserts, WithChunkReading, WithStartRow, WithValidation
{
    public function __construct(protected string $importId)
    {
        Log::info('Iniciando importação de bens', [
            'import_id' => $this->importId,
        ]);
    }

    /**
     * A primeira linha é o cabeçalho da planilha — os dados começam na linha 2.
     */
    public function startRow(): int
    {
        Log::debug('Linha inicial da importação de bens configurada', [
            'import_id' => $this->importId,
            'start_row' => 2,
        ]);

        return 2;
    }

    public function model(array $row): ?Model
    {
        [$rp, $descricao, $nomeLocal, $situacao, $elementoDespesa, $valor, $observacao] = array_pad($row, 7, null);

        $rp = trim((string) $rp);

        if ($rp === '') {
            Log::warning('Linha ignorada na importação de bens por RP vazio', [
                'import_id' => $this->importId,
                'row' => $row,
            ]);
            $this->registrarProgresso('ignorados');

            return null;
        }

        $local = null;

        if (filled($nomeLocal)) {
            $local = Local::firstOrCreate(['nome' => trim((string) $nomeLocal)]);
            Log::debug('Local validado/criado durante importação de bens', [
                'import_id' => $this->importId,
                'rp' => $rp,
                'local' => trim((string) $nomeLocal),
                'local_id' => $local?->id,
            ]);
        }

        $bem = Bem::updateOrCreate(
            ['rp' => $rp],
            [
                'descricao' => trim((string) $descricao),
                'local_id' => $local?->id,
                'ultima_situacao' => $situacao !== null ? trim((string) $situacao) : null,
                'elemento_despesa' => filled($elementoDespesa) ? trim((string) $elementoDespesa) : null,
                'valor' => $this->parseValor($valor),
                'observacao' => filled($observacao) ? trim((string) $observacao) : null,
            ]
        );

        $acao = $bem->wasRecentlyCreated ? 'criados' : 'atualizados';
        Log::info('Item processado na importação de bens', [
            'import_id' => $this->importId,
            'rp' => $rp,
            'acao' => $acao,
            'descricao' => trim((string) $descricao),
            'local' => $nomeLocal,
        ]);

        $this->registrarProgresso($acao);

        // Já salvamos manualmente com updateOrCreate (para não duplicar
        // pelo RP); retornar null diz ao Laravel Excel para não tentar
        // salvar de novo por conta própria.
       
        return null;
    }

    /**
     * Chamado pelo Laravel Excel quando uma linha falha na validação
     * (regras de rules()). Guarda no cache em vez de numa propriedade,
     * porque esse job pode ser um chunk diferente do que processa
     * o restante da planilha.
     */
    public function onFailure(Failure ...$failures): void
    {
        $chaveFalhas = "importacao_bens:{$this->importId}:falhas";
        $lock = Cache::lock("{$chaveFalhas}:lock", 10);

        $lock->block(5, function () use ($chaveFalhas, $failures) {
            $atual = Cache::get($chaveFalhas, []);

            foreach ($failures as $falha) {
                $atual[] = [
                    'linha' => $falha->row(),
                    'erros' => $falha->errors(),
                ];
            }

            Cache::put($chaveFalhas, $atual, now()->addHours(2));
        });

        Cache::increment("importacao_bens:{$this->importId}:processadas", count($failures));

        Log::warning('Falhas de validação na importação de bens', [
            'import_id' => $this->importId,
            'quantidade' => count($failures),
            'falhas' => array_map(fn (Failure $falha) => [
                'linha' => $falha->row(),
                'erros' => $falha->errors(),
            ], $failures),
        ]);
    }

    private function registrarProgresso(string $campo): void
    {
        Cache::increment("importacao_bens:{$this->importId}:processadas");
        Cache::increment("importacao_bens:{$this->importId}:{$campo}");

        Log::debug('Progresso atualizado na importação de bens', [
            'import_id' => $this->importId,
            'campo' => $campo,
            'processadas' => Cache::get("importacao_bens:{$this->importId}:processadas", 0),
        ]);
    }

    public function getResumo(): array
    {
        $falhas = Cache::get("importacao_bens:{$this->importId}:falhas", []);
        $criados = (int) Cache::get("importacao_bens:{$this->importId}:criados", 0);
        $atualizados = (int) Cache::get("importacao_bens:{$this->importId}:atualizados", 0);
        $ignorados = (int) Cache::get("importacao_bens:{$this->importId}:ignorados", 0);
        $processadas = (int) Cache::get("importacao_bens:{$this->importId}:processadas", 0);

        return [
            'criados' => $criados,
            'atualizados' => $atualizados,
            'ignorados' => $ignorados,
            'processadas' => $processadas,
            'importados' => $criados + $atualizados,
            'falhas' => is_array($falhas) ? $falhas : [],
        ];
    }

    private function parseValor(mixed $valor): ?float
    {
        if (blank($valor)) {
            Log::debug('Valor vazio ignorado durante importação de bens', [
                'import_id' => $this->importId,
                'valor' => $valor,
            ]);

            return null;
        }

        $limpo = preg_replace('/[^\d,.-]/', '', (string) $valor);
        $limpo = str_replace('.', '', $limpo);
        $limpo = str_replace(',', '.', $limpo);

        if (! is_numeric($limpo)) {
            Log::warning('Valor inválido encontrado na importação de bens', [
                'import_id' => $this->importId,
                'valor_original' => $valor,
                'valor_limpo' => $limpo,
            ]);

            return null;
        }

        return (float) $limpo;
    }

    public function rules(): array
    {
        return [
            // Índice 0 da linha = coluna A = RP
            '0' => 'required',
        ];
    }

    public function batchSize(): int
    {
        return 200;
    }

    public function chunkSize(): int
    {
        return 200;
    }
}