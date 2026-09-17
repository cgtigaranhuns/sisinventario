<?php

namespace App\Imports;

use App\Models\Bem;
use App\Models\Local;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

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
 */
class BensImport implements SkipsOnFailure, ToModel, WithBatchInserts, WithChunkReading, WithStartRow, WithValidation
{
    use SkipsFailures;

    public int $criados = 0;

    public int $atualizados = 0;

    public int $ignorados = 0;

    /**
     * A primeira linha é o cabeçalho da planilha — os dados começam na linha 2.
     */
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row): ?Model
    {
        [$rp, $descricao, $nomeLocal, $situacao, $elementoDespesa, $valor, $observacao] = array_pad($row, 7, null);

        $rp = trim((string) $rp);

        if ($rp === '') {
            $this->ignorados++;

            return null;
        }

        $local = null;

        if (filled($nomeLocal)) {
            $local = Local::firstOrCreate(['nome' => trim((string) $nomeLocal)]);
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

        $bem->wasRecentlyCreated ? $this->criados++ : $this->atualizados++;

        // Já salvamos manualmente com updateOrCreate (para não duplicar
        // pelo RP); retornar null diz ao Laravel Excel para não tentar
        // salvar de novo por conta própria.
        return null;
    }

    private function parseValor(mixed $valor): ?float
    {
        if (blank($valor)) {
            return null;
        }

        $limpo = preg_replace('/[^\d,.-]/', '', (string) $valor);
        $limpo = str_replace('.', '', $limpo);
        $limpo = str_replace(',', '.', $limpo);

        return is_numeric($limpo) ? (float) $limpo : null;
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
