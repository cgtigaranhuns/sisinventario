<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Bens</title>
    <style>
        @page {
            margin: 24px;
        }

        body {
            color: #222;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9px;
        }

        .institution {
            border-collapse: collapse;
            margin-bottom: 12px;
            width: 100%;
        }

        .institution td {
            border: 0;
            padding: 2px 0;
            vertical-align: middle;
        }

        .institution-logo {
            padding: 2px 0 !important;
            text-align: left;
            width: 50%;
        }

        .institution-logo img {
            height: auto;
            width: 280px;
        }

        .institution-side {
            width: 24%;
        }

        .institution-name {
            color: #111;
            font-size: 12px;
            line-height: 1.6;
            text-align: center;
        }

        .institution-name.title {
            font-size: 14px;
            font-weight: bold;
        }

        .institution-project {
            border-top: 1.5px solid #555;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 0.5px;
            padding-top: 2px !important;
            text-align: center;
        }

        h1 {
            font-size: 17px;
            margin: 0 0 7px;
        }

        .metadata {
            color: #555;
            margin-bottom: 8px;
        }

        .filters {
            border-collapse: collapse;
            margin-bottom: 14px;
            width: 100%;
        }

        .filters th,
        .filters td {
            border: 1px solid #bbb;
            padding: 4px 6px;
            text-align: left;
        }

        .filters th {
            background: #f2f2f2;
            width: 22%;
        }

        table {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #bbb;
            padding: 5px;
            overflow-wrap: break-word;
            vertical-align: top;
        }

        th {
            background: #e9f3ec;
            text-align: left;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        .number,
        .value {
            text-align: right;
        }

        tfoot td {
            background: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <table class="institution">
        <tr>
            <td class="institution-logo" rowspan="5">
                <img src="{{ $logo }}" alt="Logo IFPE">
            </td>
            
        </tr>
         <tr>
            <td class="institution-project">SISINVENTARIO - SISTEMA DE INVENTÁRIO</td>
        </tr>      
        
        <tr>
            <td class="institution-project">IFPE - CAMPUS GARANHUNS</td>
        </tr>
    </table>

    <h1>Relatório de Bens</h1>
    <div class="metadata">
        Gerado em {{ $geradoEm->format('d/m/Y H:i') }} |
        Quantidade de bens: {{ $bens->count() }}
    </div>

    <table class="filters">
        <tbody>
            @forelse ($appliedFilters as $label => $value)
                <tr>
                    <th>FILTRO: {{ $label }}</th>
                    <td>{{ $value }}</td>
                </tr>
            @empty
                <tr>
                    <th>Filtros</th>
                    <td>Nenhum filtro aplicado (todos os bens).</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 7%">RP</th>
                <th style="width: 24%">Descrição</th>
                <th style="width: 15%">Local</th>
                <th style="width: 12%">Última situação</th>
                <th style="width: 14%">Elemento de despesa</th>
                <th class="value" style="width: 10%">Valor</th>
                <th style="width: 18%">Observação</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bens as $bem)
                <tr>
                    <td class="number">{{ $bem->rp }}</td>
                    <td>{{ $bem->descricao }}</td>
                    <td>{{ $bem->local?->nome ?? '—' }}</td>
                    <td>{{ $bem->ultima_situacao ?? '—' }}</td>
                    <td>{{ $bem->elemento_despesa ?? '—' }}</td>
                    <td class="value">
                        {{ $bem->valor === null ? '—' : 'R$ ' . number_format((float) $bem->valor, 2, ',', '.') }}
                    </td>
                    <td>{{ $bem->observacao ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Nenhum bem encontrado com os filtros informados.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">Total</td>
                <td class="value">R$ {{ number_format($total, 2, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>