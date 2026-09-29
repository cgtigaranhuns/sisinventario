<x-filament-widgets::widget>
    <style>
        .cb-card {
            display: block;
            text-decoration: none;
            border-radius: 1rem;
            overflow: hidden;
            background: #fff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .cb-card:hover {
            transform: translateY(-3px);
            border-color: #3CB371;
            box-shadow: 0 12px 24px -8px rgba(60, 179, 113, .35);
        }
        .cb-top {
            height: 6px;
            background: linear-gradient(90deg, #3CB371, #2e8b57);
        }
        .cb-body { padding: 1.25rem; }
        .cb-head { display: flex; align-items: center; gap: 1rem; }
        .cb-badge {
            flex-shrink: 0;
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: linear-gradient(135deg, #3CB371, #2e8b57);
            box-shadow: 0 6px 14px -4px rgba(60, 179, 113, .6);
        }
        .cb-title { font-size: 1.05rem; font-weight: 700; color: #111827; margin: 0; }
        .cb-sub { font-size: .85rem; color: #6b7280; margin: .15rem 0 0; }
        .cb-chips { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: 1rem; }
        .cb-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .3rem .65rem;
            font-size: .75rem;
            font-weight: 500;
            color: #2e8b57;
            background: #ecfdf3;
            border-radius: 9999px;
        }
        .cb-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1.1rem;
            padding-top: .9rem;
            border-top: 1px solid #f3f4f6;
            font-size: .875rem;
            font-weight: 600;
            color: #2e8b57;
        }
        .cb-arrow { transition: transform .2s ease; }
        .cb-card:hover .cb-arrow { transform: translateX(4px); }
    </style>

    <a href="{{ $url }}" wire:navigate class="cb-card">
        <div class="cb-top"></div>

        <div class="cb-body">
            <div class="cb-head">
                <div class="cb-badge">
                    <x-filament::icon
                        icon="heroicon-o-clipboard-document-check"
                        style="width: 1.9rem; height: 1.9rem;"
                    />
                </div>

                <div>
                    <h3 class="cb-title">Conferência de bens</h3>
                    <p class="cb-sub">Confira os bens do seu local no inventário em andamento</p>
                </div>
            </div>

            <div class="cb-chips">
                <span class="cb-chip">
                    <x-filament::icon icon="heroicon-m-qr-code" style="width: 1rem; height: 1rem;" />
                    Código de barras
                </span>
                <span class="cb-chip">
                    <x-filament::icon icon="heroicon-m-microphone" style="width: 1rem; height: 1rem;" />
                    Busca por voz
                </span>
                <span class="cb-chip">
                    <x-filament::icon icon="heroicon-m-check-badge" style="width: 1rem; height: 1rem;" />
                    Confirmação em lote
                </span>
            </div>

            <div class="cb-foot">
                <span>Abrir conferência</span>
                <x-filament::icon
                    icon="heroicon-m-arrow-right"
                    class="cb-arrow"
                    style="width: 1.25rem; height: 1.25rem;"
                />
            </div>
        </div>
    </a>
</x-filament-widgets::widget>