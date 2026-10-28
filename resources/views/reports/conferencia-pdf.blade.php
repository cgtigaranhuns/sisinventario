<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Andamento da Conferência</title>
    <style>
        @page {
            margin: 24px;
        }

        body {
            color: #222;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9px;
        }

        h1,
        h2,
        p {
            margin: 0;
        }

        h1 {
            font-size: 17px;
            margin-bottom: 7px;
        }

        h2 {
            font-size: 12px;
        }

        .institution,
        .summary,
        .locais {
            border-collapse: collapse;
            width: 100%;
        }

        .items {
            border-collapse: separate;
            border-spacing: 0;
            border-top: 1px solid #bbb;
            border-left: 1px solid #bbb;
            table-layout: fixed;
            width: 100%;
        }

        .institution {
            margin-bottom: 16px;
        }

        .institution td {
            border: 0;
            padding: 3px;
            text-align: center;
        }

        .institution img {
            height: auto;
            max-width: 280px;
        }

        .institution-name {
            font-size: 14px;
            font-weight: bold;
        }

        .metadata {
            color: #555;
            margin-bottom: 10px;
        }

        .summary {
            margin-bottom: 18px;
        }

        .summary th,
        .summary td,
        .locais th,
        .locais td {
            border: 1px solid #bbb;
            padding: 5px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }

        .items th,
        .items td {
            border-right: 1px solid #bbb;
            border-bottom: 1px solid #bbb;
            padding: 5px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }

        .summary th,
        .locais th,
        .items th {
            background: #e9f3ec;
        }

        .summary td,
        .locais td.num {
            text-align: center;
        }

        .section-title {
            margin-bottom: 6px;
            margin-top: 16px;
        }

        .local-summary {
            color: #555;
            margin-bottom: 6px;
        }

        .empty {
            color: #555;
            margin-top: 12px;
        }
    </style>
</head>
<body>
    <table class="institution">
        <tr>
            @if($logo)
                <td><img src="{{ $logo }}" alt="Logo IFPE"></td>
            @endif
            <td class="institution-name">SISINVENTARIO - IFPE CAMPUS GARANHUNS</td>
        </tr>
    </table>

    <h1>Relatório de andamento da conferência</h1>
    <div class="metadata">
        Inventário: {{ $inventario->titulo }} |
        Status: {{ $inventario->status ?? '—' }} |
        Gerado em {{ $geradoEm->format('d/m/Y H:i') }}
    </div>

    <table class="summary">
        <thead>
            <tr>
                <th>Total de itens</th>
                <th>Conferidos</th>
                <th>Pendentes</th>
                <th>Progresso</th>
                <th>Locais com itens</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ number_format($total, 0, ',', '.') }}</td>
                <td>{{ number_format($conferidos, 0, ',', '.') }}</td>
                <td>{{ number_format($pendentes, 0, ',', '.') }}</td>
                <td>{{ number_format($percentual, 1, ',', '.') }}%</td>
                <td>{{ number_format($locais->count(), 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    @if($detalhe)
        <h2 class="section-title">{{ $detalhe['nome'] }}</h2>
        <p class="local-summary">{{ number_format($detalhe['itens']->count(), 0, ',', '.') }} itens neste local</p>

        <table class="items">
            <thead>
                <tr>
                    <th style="width: 7%">RP</th>
                    <th style="width: 27%">Bem</th>
                    <th style="width: 14%">Andamento</th>
                    <th style="width: 14%">Situação</th>
                    <th style="width: 14%">Conferido em</th>
                    <th style="width: 14%">Conferido por</th>
                    <th style="width: 10%">Observação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detalhe['itens'] as $conferencia)
                    <tr>
                        <td>{{ $conferencia->bem?->rp ?? '—' }}</td>
                        <td>{{ $conferencia->bem?->descricao ?? 'Bem não encontrado' }}</td>
                        <td>{{ $conferencia->conferido_em ? 'Conferido' : 'Pendente' }}</td>
                        <td>{{ $conferencia->situacao ?? '—' }}</td>
                        <td>{{ $conferencia->conferido_em ? \Illuminate\Support\Carbon::parse($conferencia->conferido_em)->format('d/m/Y H:i') : '—' }}</td>
                        <td>{{ $conferencia->conferidoPor?->name ?? '—' }}</td>
                        <td>{{ $conferencia->observacao ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <h2 class="section-title">Andamento por local</h2>

        @if($locais->isEmpty())
            <p class="empty">Nenhum item foi registrado para este inventário.</p>
        @else
            <table class="locais">
                <thead>
                    <tr>
                        <th style="width: 40%">Local</th>
                        <th style="width: 12%">Total</th>
                        <th style="width: 12%">Conferidos</th>
                        <th style="width: 12%">Pendentes</th>
                        <th style="width: 12%">Progresso</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($locais as $local)
                        <tr>
                            <td>{{ $local['nome'] }}</td>
                            <td class="num">{{ number_format($local['total'], 0, ',', '.') }}</td>
                            <td class="num">{{ number_format($local['conferidos'], 0, ',', '.') }}</td>
                            <td class="num">{{ number_format($local['pendentes'], 0, ',', '.') }}</td>
                            <td class="num">{{ number_format($local['percentual'], 1, ',', '.') }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif
</body>
</html>