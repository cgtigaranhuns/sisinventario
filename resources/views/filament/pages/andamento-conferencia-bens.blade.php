<x-filament-panels::page>
    <style>
        .acb-wrap{--acb-accent:#0E7C86;--acb-success:#15803D;--acb-success-bg:#EAF6EE;--acb-success-border:#BFE5CC;
            --acb-warning:#B45309;--acb-warning-bg:#FDF2E2;--acb-warning-border:#F3D8AB;--acb-track:#E9EBEE;
            --acb-ink-faint:#8A93A0;}
        .dark .acb-wrap{--acb-accent:#3FB8C2;--acb-success:#4ADE80;--acb-success-bg:rgba(74,222,128,0.10);
            --acb-success-border:rgba(74,222,128,0.28);--acb-warning:#FBBF6D;--acb-warning-bg:rgba(251,191,109,0.10);
            --acb-warning-border:rgba(251,191,109,0.28);--acb-track:#262C34;--acb-ink-faint:#6B7480;}

        .acb-metrics{display:flex;flex-wrap:wrap;}
        .acb-metric{flex:1 1 200px;padding:1.1rem 1.4rem;}
        .acb-metric + .acb-metric{border-left:1px solid rgb(var(--gray-200)/1);}
        .dark .acb-metric + .acb-metric{border-left-color:rgb(var(--gray-700)/1);}
        .acb-metric .acb-label{font-size:.82rem;color:rgb(var(--gray-500));margin-bottom:.4rem;}
        .acb-metric .acb-value{font-size:1.75rem;font-weight:600;font-variant-numeric:tabular-nums;line-height:1;}
        .acb-metric .acb-sub{margin-top:.35rem;font-size:.8rem;font-variant-numeric:tabular-nums;}
        .acb-metric .acb-sub.ok{color:var(--acb-success);}
        .acb-metric .acb-sub.warn{color:var(--acb-warning);}
        .acb-metric .acb-sub.neutral{color:var(--acb-ink-faint);}

        .acb-pct-pill{font-size:.78rem;font-weight:600;color:var(--acb-success);background:var(--acb-success-bg);
            border:1px solid var(--acb-success-border);border-radius:999px;padding:.15rem .6rem;}

        .acb-legend{margin-top:1.1rem;padding-top:.9rem;border-top:1px solid rgb(var(--gray-100));display:flex;
            flex-direction:column;gap:.5rem;font-size:.85rem;}
        .dark .acb-legend{border-top-color:rgb(var(--gray-700)/.5);}
        .acb-legend .acb-row{display:flex;align-items:center;justify-content:space-between;color:rgb(var(--gray-500));}
        .acb-legend .acb-row .acb-dot{display:inline-block;width:9px;height:9px;border-radius:50%;margin-right:.5rem;}
        .acb-legend .acb-row .acb-val{color:rgb(var(--gray-950));font-weight:500;font-variant-numeric:tabular-nums;}
        .dark .acb-legend .acb-row .acb-val{color:#fff;}

        .acb-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.6rem;
            height:230px;color:var(--acb-ink-faint);text-align:center;font-size:.85rem;}

        table.acb-locais{width:100%;border-collapse:collapse;table-layout:fixed;}
        table.acb-locais colgroup col.c1{width:26%;} table.acb-locais colgroup col.c2{width:30%;}
        table.acb-locais colgroup col.c3{width:16%;} table.acb-locais colgroup col.c4{width:14%;}
        table.acb-locais colgroup col.c5{width:14%;}
        table.acb-locais thead th{text-align:left;font-size:.78rem;font-weight:500;color:rgb(var(--gray-500));
            padding:0 1rem .6rem 0;border-bottom:1px solid rgb(var(--gray-200));}
        .dark table.acb-locais thead th{border-bottom-color:rgb(var(--gray-700)/1);}
        table.acb-locais thead th.num{text-align:right;}
        table.acb-locais tbody td{padding:.9rem 1rem .9rem 0;border-bottom:1px solid rgb(var(--gray-100));
            font-size:.88rem;vertical-align:middle;}
        .dark table.acb-locais tbody td{border-bottom-color:rgb(var(--gray-700)/.5);}
        table.acb-locais tbody tr:last-child td{border-bottom:none;}
        table.acb-locais tbody td.num{text-align:right;font-variant-numeric:tabular-nums;color:rgb(var(--gray-500));}
        table.acb-locais tbody td.num.strong{color:rgb(var(--gray-950));}
        .dark table.acb-locais tbody td.num.strong{color:#fff;}
        .acb-local-name{display:flex;align-items:center;gap:.55rem;font-weight:500;}
        .acb-bar-track{height:7px;border-radius:999px;background:var(--acb-track);overflow:hidden;}
        .acb-bar-fill{height:100%;border-radius:999px;background:var(--acb-success);}
        .acb-badge{display:inline-block;font-size:.78rem;font-weight:600;padding:.18rem .6rem;border-radius:999px;
            background:rgb(var(--gray-50));border:1px solid rgb(var(--gray-200));color:rgb(var(--gray-500));}
        .dark .acb-badge{background:transparent;border-color:rgb(var(--gray-700)/1);}
        .acb-badge.done{background:var(--acb-success-bg);border-color:var(--acb-success-border);color:var(--acb-success);}
    </style>

    <div class="acb-wrap">
        {{-- Filtro de inventário --}}
        <x-filament::section compact icon="heroicon-o-adjustments-horizontal" icon-color="gray">
            <x-slot name="heading">
                Inventário
            </x-slot>

            {{ $this->form }}
        </x-filament::section>

        @php($inventario = $this->inventarioSelecionado())
        @php($resumo = $this->resumo())
        @php($situacoes = $this->situacoesResumo()->toArray())
        @php($locais = $this->locaisResumo()->toArray())
        @php($pctFmt = fn ($v) => str_replace('.', ',', number_format((float) $v, 1)))

        @if($inventario)
            {{-- KPIs --}}
            <x-filament::section compact class="mt-6">
                <div class="acb-metrics">
                    <div class="acb-metric">
                        <div class="acb-label">Itens no inventário</div>
                        <div class="acb-value">{{ number_format($resumo['total'], 0, ',', '.') }}</div>
                        <div class="acb-sub neutral">total cadastrado</div>
                    </div>
                    <div class="acb-metric">
                        <div class="acb-label">Conferidos</div>
                        <div class="acb-value">{{ number_format($resumo['conferidos'], 0, ',', '.') }}</div>
                        <div class="acb-sub ok">{{ $pctFmt($resumo['percentual']) }}% do total</div>
                    </div>
                    <div class="acb-metric">
                        <div class="acb-label">Pendentes</div>
                        <div class="acb-value">{{ number_format($resumo['pendentes'], 0, ',', '.') }}</div>
                        <div class="acb-sub warn">{{ $pctFmt(100 - $resumo['percentual']) }}% do total</div>
                    </div>
                </div>
            </x-filament::section>

            {{-- Gráficos --}}
            <div class="mt-6 grid gap-4 xl:grid-cols-2">
                <x-filament::section>
                    <x-slot name="heading">Resumo geral</x-slot>
                    <x-slot name="headerEnd">
                        <span class="acb-pct-pill">{{ $pctFmt($resumo['percentual']) }}%</span>
                    </x-slot>

                    <div class="mx-auto max-w-[170px]">
                        <canvas id="conferenciaGeralChart" aria-label="Resumo geral da conferência" role="img"></canvas>
                    </div>

                    <div class="acb-legend">
                        <div class="acb-row">
                            <span><span class="acb-dot" style="background:var(--acb-success)"></span>Conferidos</span>
                            <span class="acb-val">{{ number_format($resumo['conferidos'], 0, ',', '.') }}</span>
                        </div>
                        <div class="acb-row">
                            <span><span class="acb-dot" style="background:var(--acb-track)"></span>Pendentes</span>
                            <span class="acb-val">{{ number_format($resumo['pendentes'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">Itens por situação</x-slot>

                    @if(! empty($situacoes))
                        <div style="height:230px;">
                            <canvas id="situacaoConferenciaChart" aria-label="Itens por situação" role="img"></canvas>
                        </div>
                    @else
                        <div class="acb-empty">
                            <x-filament::icon icon="heroicon-o-clipboard-document-list" class="h-8 w-8" />
                            Ainda não há itens conferidos para este inventário.
                        </div>
                    @endif
                </x-filament::section>
            </div>

            {{-- Detalhamento por local --}}
            <x-filament::section class="mt-6">
                <x-slot name="heading">Detalhamento por local</x-slot>
                <x-slot name="headerEnd">
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ count($locais) }} {{ count($locais) === 1 ? 'local' : 'locais' }}</span>
                </x-slot>

                @if(! empty($locais))
                    <div class="overflow-x-auto">
                        <table class="acb-locais">
                            <colgroup>
                                <col class="c1"><col class="c2"><col class="c3"><col class="c4"><col class="c5">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Local</th>
                                    <th>Progresso</th>
                                    <th class="num">Conferidos</th>
                                    <th class="num">Pendentes</th>
                                    <th class="num">Percentual</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($locais as $local)
                                    <tr>
                                        <td>
                                            <span class="acb-local-name">
                                                <x-filament::icon icon="heroicon-o-map-pin" class="h-4 w-4 shrink-0 text-gray-400" />
                                                <span class="truncate">{{ $local['nome'] }}</span>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="acb-bar-track">
                                                <div class="acb-bar-fill" style="width: {{ min($local['percentual'], 100) }}%"></div>
                                            </div>
                                        </td>
                                        <td class="num strong">{{ $local['conferidos'] }} de {{ $local['total'] }}</td>
                                        <td class="num">{{ $local['pendentes'] }}</td>
                                        <td class="num">
                                            <span class="acb-badge {{ $local['percentual'] >= 100 ? 'done' : '' }}">
                                                {{ $pctFmt($local['percentual']) }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="acb-empty">
                        <x-filament::icon icon="heroicon-o-inbox" class="h-8 w-8" />
                        Nenhum item foi registrado para este inventário.
                    </div>
                @endif
            </x-filament::section>
        @else
            <div class="mt-6 flex flex-col items-center justify-center gap-3 rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center dark:border-white/10 dark:bg-gray-900">
                <x-filament::icon icon="heroicon-o-clipboard-document-check" class="h-10 w-10 text-gray-300 dark:text-gray-600" />
                <p class="text-sm text-gray-500 dark:text-gray-400">Selecione um inventário para visualizar o andamento da conferência.</p>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const geralChart = document.getElementById('conferenciaGeralChart');
            const situacaoChart = document.getElementById('situacaoConferenciaChart');

            const resumo = @json($resumo ?? []);
            const situacoes = @json($situacoes ?? []);

            Chart.defaults.font.family = "'Inter var', ui-sans-serif, system-ui, sans-serif";
            Chart.defaults.color = '#8A93A0';

            if (geralChart) {
                new Chart(geralChart, {
                    type: 'doughnut',
                    data: {
                        labels: ['Conferidos', 'Pendentes'],
                        datasets: [{
                            data: [resumo.conferidos, resumo.pendentes],
                            backgroundColor: ['#15803D', '#E9EBEE'],
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return `${context.label}: ${context.parsed} itens`;
                                    },
                                },
                            },
                        },
                    },
                });
            }

            if (situacaoChart && situacoes.length) {
                const palette = {
                    'Servível': '#15803D',
                    'Inservível': '#f97316',
                    
                };

                new Chart(situacaoChart, {
                    type: 'bar',
                    data: {
                        labels: situacoes.map(item => item.situacao),
                        datasets: [{
                            label: 'Itens',
                            data: situacoes.map(item => item.total),
                            backgroundColor: situacoes.map(item => palette[item.situacao] ?? '#15803D'),
                            borderRadius: 5,
                            maxBarThickness: 22,
                        }],
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: { precision: 0 },
                                grid: { color: 'rgba(138,147,160,0.15)' },
                            },
                            y: { grid: { display: false } },
                        },
                    },
                });
            }
        });
    </script>
</x-filament-panels::page>