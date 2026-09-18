<x-filament-panels::page>

    <style>
        .import-planilha-card {
            background: var(--fi-color-gray-50, #f9fafb);
            border: 1px solid var(--fi-color-gray-200, #e5e7eb);
            border-radius: 1rem;
            padding: 1.5rem;
        }

        .dark .import-planilha-card {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.08);
        }

        .import-planilha-heading {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .dark .import-planilha-heading {
            color: #f3f4f6;
        }

        .import-planilha-desc {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 1.5rem;
        }

        .dark .import-planilha-desc {
            color: #9ca3af;
        }

        .import-planilha-table-wrap {
            overflow-x: auto;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
        }

        .dark .import-planilha-table-wrap {
            border-color: rgba(255, 255, 255, 0.08);
        }

        .import-planilha-table {
            width: 100%;
            min-width: 760px;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .import-planilha-table th,
        .import-planilha-table td {
            border: 1px solid #e5e7eb;
            padding: 0.6rem 0.75rem;
            text-align: center;
            vertical-align: top;
        }

        .dark .import-planilha-table th,
        .dark .import-planilha-table td {
            border-color: rgba(255, 255, 255, 0.08);
        }

        .import-planilha-table thead tr {
            background: #f9fafb;
        }

        .dark .import-planilha-table thead tr {
            background: rgba(255, 255, 255, 0.05);
        }

        .import-planilha-table thead th {
            font-family: ui-monospace, monospace;
            font-weight: 700;
            color: #6b7280;
        }

        .dark .import-planilha-table thead th {
            color: #9ca3af;
        }

        .import-planilha-row-titulo {
            background: rgba(79, 70, 229, 0.06);
        }

        .dark .import-planilha-row-titulo {
            background: rgba(129, 140, 248, 0.08);
        }

        .import-planilha-row-titulo td {
            font-weight: 600;
            color: #1f2937;
        }

        .dark .import-planilha-row-titulo td {
            color: #f3f4f6;
        }

        .import-planilha-row-ajuda td {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .dark .import-planilha-row-ajuda td {
            color: #9ca3af;
        }

        .import-planilha-obrigatorio {
            color: #dc2626;
        }

        .dark .import-planilha-obrigatorio {
            color: #f87171;
        }

        .import-planilha-legenda {
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: #6b7280;
        }

        .dark .import-planilha-legenda {
            color: #9ca3af;
        }

        .import-planilha-alerta {
            margin-top: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            background: #fffbeb;
            border: 1px solid #fde68a;
        }

        .dark .import-planilha-alerta {
            background: rgba(251, 191, 36, 0.08);
            border-color: rgba(251, 191, 36, 0.2);
        }

        .import-planilha-alerta svg {
            width: 1.25rem;
            height: 1.25rem;
            flex-shrink: 0;
            margin-top: 0.125rem;
            color: #d97706;
        }

        .dark .import-planilha-alerta svg {
            color: #fbbf24;
        }

        .import-planilha-alerta p {
            font-size: 0.875rem;
            color: #92400e;
        }

        .dark .import-planilha-alerta p {
            color: #fbbf24;
        }

        .import-planilha-upload-box {
            margin-top: 1rem;
        }
    </style>

    @php
        $colunas = [
            ['letra' => 'A', 'titulo' => 'RP', 'ajuda' => 'número de patrimônio', 'obrigatorio' => true],
            ['letra' => 'B', 'titulo' => 'Descrição', 'ajuda' => null, 'obrigatorio' => true],
            ['letra' => 'C', 'titulo' => 'Local', 'ajuda' => 'criado automaticamente se não existir', 'obrigatorio' => false],
            ['letra' => 'D', 'titulo' => 'Situação', 'ajuda' => null, 'obrigatorio' => false],
            ['letra' => 'E', 'titulo' => 'Elemento de Despesa', 'ajuda' => null, 'obrigatorio' => false],
            ['letra' => 'F', 'titulo' => 'Valor', 'ajuda' => '"1234,56" ou "1234.56"', 'obrigatorio' => false],
            ['letra' => 'G', 'titulo' => 'Observação', 'ajuda' => null, 'obrigatorio' => false],
        ];
    @endphp

    <x-filament::section>
        <div class="import-planilha-card">
            <div class="import-planilha-heading">
                <x-filament::icon icon="heroicon-o-table-cells" class="w-5 h-5 text-primary-600" />
                Como montar a planilha
            </div>

            <p class="import-planilha-desc">
                A primeira linha deve ser o cabeçalho (título das colunas) — ela é ignorada na importação.
                Os dados começam na linha 2, seguindo exatamente esta ordem de colunas:
            </p>

            <div class="import-planilha-table-wrap">
                <table class="import-planilha-table">
                    {{-- Linha 1: letras da coluna, como no Excel --}}
                    <thead>
                        <tr>
                            @foreach ($colunas as $coluna)
                                <th>{{ $coluna['letra'] }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        {{-- Linha 2: nome do campo --}}
                        <tr class="import-planilha-row-titulo">
                            @foreach ($colunas as $coluna)
                                <td>
                                    {{ $coluna['titulo'] }}
                                    @if ($coluna['obrigatorio'])
                                        <span class="import-planilha-obrigatorio">*</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>

                        {{-- Linha 3: dica de preenchimento --}}
                        <tr class="import-planilha-row-ajuda">
                            @foreach ($colunas as $coluna)
                                <td>{{ $coluna['ajuda'] ?? '—' }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="import-planilha-legenda">
                <span class="import-planilha-obrigatorio" style="font-weight: 600;">*</span>
                Campo obrigatório. Os demais são opcionais.
            </p>

            <div class="import-planilha-alerta">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="9" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                <p>
                    Bens com o mesmo RP de um já cadastrado são <strong>atualizados</strong>, não duplicados.
                </p>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section class="mt-6">
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-arrow-up-tray" class="w-5 h-5 text-primary-600" />
                Enviar planilha
            </div>
        </x-slot>

        <form wire:submit="importar" class="import-planilha-upload-box">
            {{ $this->form }}

            <x-filament::button type="submit" icon="heroicon-o-arrow-up-tray" class="mt-4">
                Importar
            </x-filament::button>
        </form>
    </x-filament::section>

</x-filament-panels::page>