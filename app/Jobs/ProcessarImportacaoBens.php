<?php

namespace App\Jobs;

use App\Imports\BensImport;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;


class ProcessarImportacaoBens implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Tempo máximo (segundos) que o worker pode gastar nesse job.
     * Ajuste conforme o tamanho esperado das planilhas.
     */
    public int $timeout = 1800; // 30 minutos

    public function __construct(
        public string $caminhoRelativo,
        public int $usuarioId,
    ) {}

    public function handle(): void
    {
        Log::info("ProcessarImportacaoBens: iniciando job para usuário #{$this->usuarioId}, arquivo {$this->caminhoRelativo}");

        $usuario = User::find($this->usuarioId);

        if (! $usuario) {
            Log::warning("ProcessarImportacaoBens: usuário #{$this->usuarioId} não encontrado — a notificação NÃO será registrada.");
        }

        try {
            $caminhoAbsoluto = Storage::disk('local')->path($this->caminhoRelativo);

            $import = new BensImport($this->usuarioId);
            Excel::import($import, $caminhoAbsoluto);

            $resumo = $import->getResumo();

            Log::info('ProcessarImportacaoBens: importação concluída.', $resumo);

            Storage::disk('local')->delete($this->caminhoRelativo);

            if ($usuario) {
                $resultado = Notification::make()
                    ->title('Importação concluída')
                    ->body('Bens importados com sucesso. Confira o cadastro dos bens.')
                    ->success()
                    ->sendToDatabase($usuario);

                Log::info('ProcessarImportacaoBens: notificação de conclusão gravada com id '.($resultado->id ?? 'desconhecido'));
            }

            if (! empty($resumo['falhas'])) {
                $linhas = collect($resumo['falhas'])
                    ->map(fn (array $falha) => "linha {$falha['linha']}")
                    ->implode(', ');

                if ($usuario) {
                    Notification::make()
                        ->title('Algumas linhas não passaram na validação')
                        ->body("Confira: {$linhas}.")
                        ->warning()
                        ->sendToDatabase($usuario);

                    Log::info('ProcessarImportacaoBens: notificação de falhas de validação gravada.');
                }
            }
        } catch (Throwable $e) {
            Log::error('ProcessarImportacaoBens: falha ao importar planilha de bens: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            if ($usuario) {
                Notification::make()
                    ->title('Falha na importação')
                    ->body('Ocorreu um erro ao processar a planilha: '.$e->getMessage())
                    ->danger()
                    ->persistent()
                    ->sendToDatabase($usuario);
            }

            Storage::disk('local')->delete($this->caminhoRelativo);
        }
    }
}