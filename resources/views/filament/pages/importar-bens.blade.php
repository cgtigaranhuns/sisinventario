<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Como montar a planilha
        </x-slot>

        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            A primeira linha deve ser o cabeçalho (título das colunas) — ela é ignorada na importação.
            Os dados começam na linha 2. As colunas devem seguir exatamente esta ordem, da esquerda para a direita:
        </p>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border border-gray-200 dark:border-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-3 py-2 text-left border-b border-gray-200 dark:border-gray-700">Coluna</th>
                        <th class="px-3 py-2 text-left border-b border-gray-200 dark:border-gray-700">Conteúdo</th>
                        <th class="px-3 py-2 text-left border-b border-gray-200 dark:border-gray-700">Obrigatório</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="px-3 py-2 font-mono">A</td>
                        <td class="px-3 py-2">RP (número de patrimônio)</td>
                        <td class="px-3 py-2">Sim</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono">B</td>
                        <td class="px-3 py-2">Descrição</td>
                        <td class="px-3 py-2">Sim</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono">C</td>
                        <td class="px-3 py-2">Local (nome — é criado automaticamente se não existir)</td>
                        <td class="px-3 py-2">Não</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono">D</td>
                        <td class="px-3 py-2">Situação</td>
                        <td class="px-3 py-2">Não</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono">E</td>
                        <td class="px-3 py-2">Elemento de Despesa</td>
                        <td class="px-3 py-2">Não</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono">F</td>
                        <td class="px-3 py-2">Valor (aceita "1234,56" ou "1234.56")</td>
                        <td class="px-3 py-2">Não</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono">G</td>
                        <td class="px-3 py-2">Observação</td>
                        <td class="px-3 py-2">Não</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-sm text-gray-600 dark:text-gray-400 mt-4">
            Bens com o mesmo RP de um já cadastrado são <strong>atualizados</strong>, não duplicados.
        </p>
    </x-filament::section>

    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Enviar planilha
        </x-slot>

        <form wire:submit="importar">
            {{ $this->form }}

            <x-filament::button type="submit" class="mt-4">
                Importar
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-panels::page>