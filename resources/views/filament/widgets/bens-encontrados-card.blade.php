<x-filament-widgets::widget>
    <style>
        .be-card {
            display: block;
            text-decoration: none;
            border-radius: 1rem;
            overflow: hidden;
            background: #fff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .be-card:hover {
            transform: translateY(-3px);
            border-color: #f59e0b;
            box-shadow: 0 12px 24px -8px rgba(245, 158, 11, .4);
        }
        .be-top { height: 6px; background: linear-gradient(90deg, #f59e0b, #d97706); }
        .be-body { padding: 1.25rem; }
        .be-head { display: flex; align-items: center; gap: 1rem; }
        .be-badge {
            flex-shrink: 0;
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 6px 14px -4px rgba(245, 158, 11, .6);
        }
        .be-title { font-size: 1.05rem; font-weight: 700; color: #111827; margin: 0; }
        .be-sub { font-size: .85rem; color: #6b7280; margin: .15rem 0 0; }
        .be-chips { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: 1rem; }
        .be-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .3rem .65rem;
            font-size: .75rem;
            font-weight: 500;
            color: #b45309;
            background: #fffbeb;
            border-radius: 9999px;
        }
        .be-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1.1rem;
            padding-top: .9rem;
            border-top: 1px solid #f3f4f6;
            font-size: .875rem;
            font-weight: 600;
            color: #b45309;
        }
        .be-arrow { transition: transform .2s ease; }
        .be-card:hover .be-arrow { transform: translateX(4px); }
    </style>

    <a href="{{ $url }}" wire:navigate class="be-card">
        <div class="be-top"></div>

        <div class="be-body">
            <div class="be-head">
                <div class="be-badge">
                    <x-filament::icon
                        icon="heroicon-o-cube"
                        style="width: 1.9rem; height: 1.9rem;"
                    />
                </div>

                <div>
                    <h3 class="be-title">Bens encontrados</h3>
                    <p class="be-sub">Registre bens localizados fora do cadastro ou do local de origem</p>
                </div>
            </div>

            <div class="be-chips">
                <span class="be-chip">
                    <x-filament::icon icon="heroicon-m-camera" style="width: 1rem; height: 1rem;" />
                    Foto do bem
                </span>
                <span class="be-chip">
                    <x-filament::icon icon="heroicon-m-map-pin" style="width: 1rem; height: 1rem;" />
                    Local
                </span>
                <span class="be-chip">
                    <x-filament::icon icon="heroicon-m-clock" style="width: 1rem; height: 1rem;" />
                    Pendente / Resolvido
                </span>
            </div>

            <div class="be-foot">
                <span>Abrir bens encontrados</span>
                <x-filament::icon
                    icon="heroicon-m-arrow-right"
                    class="be-arrow"
                    style="width: 1.25rem; height: 1.25rem;"
                />
            </div>
        </div>
    </a>
</x-filament-widgets::widget>