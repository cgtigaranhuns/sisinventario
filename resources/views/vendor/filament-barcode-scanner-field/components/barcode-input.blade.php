@php
    use Filament\Support\Facades\FilamentAsset;
    use function Filament\Support\prepare_inherited_attributes;
    $fieldWrapperView = $getFieldWrapperView();
    $datalistOptions = $getDatalistOptions();
    $extraAlpineAttributes = $getExtraAlpineAttributes();
    $extraAttributeBag = $getExtraAttributeBag();
    $hasInlineLabel = $hasInlineLabel();
    $id = $getId();
    $isConcealed = $isConcealed();
    $isDisabled = $isDisabled();
    $statePath = $getStatePath();
    $placeholder = $getPlaceholder();
    $modalId = 'qrcode-scanner-modal-' . $getName();
    $readerId = 'reader-' . $getName();

    $inputAttributes = $getExtraInputAttributeBag()
            ->merge($extraAlpineAttributes, escape: false)
            ->merge([
                'autofocus' => $isAutofocused(),
                'disabled' => $isDisabled,
                'id' => $id,
                'inputmode' => $getInputMode(),
                'list' => $datalistOptions ? $id . '-list' : null,
                'max' => (! $isConcealed) ? $getMaxValue() : null,
                'maxlength' => (! $isConcealed) ? $getMaxLength() : null,
                'min' => (! $isConcealed) ? $getMinValue() : null,
                'minlength' => (! $isConcealed) ? $getMinLength() : null,
                'placeholder' => filled($placeholder) ? e($placeholder) : null,
                'readonly' => $isReadOnly(),
                'required' => $isRequired() && (! $isConcealed),
                'type' => "text",
                $applyStateBindingModifiers('wire:model') => $statePath,
            ], escape: false)
            ->class([
                'w-full pr-10',
            ]);
@endphp

@once
    <style>
        .bsf-stage {
            position: relative;
            width: 100%;
            min-height: 18rem;
            overflow: hidden;
            border-radius: 0.875rem;
            background: #101418;
            box-shadow: inset 0 0 0 1px rgb(255 255 255 / 0.06);
        }

        .bsf-reader {
            width: 100%;
            border: 0 !important;
        }

        .bsf-reader video {
            width: 100% !important;
            height: auto !important;
            min-height: 18rem;
            object-fit: cover;
            display: block;
        }

        .bsf-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1.5rem;
            text-align: center;
            color: #e5e7eb;
            background: #101418;
            font-size: 0.875rem;
            line-height: 1.4rem;
        }

        .bsf-overlay svg {
            width: 2rem;
            height: 2rem;
            color: #9ca3af;
        }

        .bsf-overlay--error svg {
            color: #f87171;
        }

        .bsf-spinner {
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 9999px;
            border: 3px solid rgb(255 255 255 / 0.15);
            border-top-color: rgb(var(--primary-400, 251 191 36));
            animation: bsf-spin 0.8s linear infinite;
        }

        @keyframes bsf-spin {
            to { transform: rotate(360deg); }
        }

        @media (prefers-reduced-motion: reduce) {
            .bsf-spinner { animation-duration: 2.4s; }
        }

        .bsf-retry {
            padding: 0.4rem 0.9rem;
            border-radius: 0.5rem;
            font-weight: 600;
            color: #111827;
            background: #f3f4f6;
        }

        .bsf-retry:hover { background: #ffffff; }

        .bsf-retry:focus-visible {
            outline: 2px solid rgb(var(--primary-400, 251 191 36));
            outline-offset: 2px;
        }

        .bsf-hint {
            margin-top: 0.875rem;
            text-align: center;
            font-size: 0.8125rem;
            line-height: 1.3rem;
            color: rgb(var(--gray-500, 107 114 128));
        }

        .dark .bsf-hint {
            color: rgb(var(--gray-400, 156 163 175));
        }

        .bsf-subtitle {
            margin-top: 0.125rem;
            font-size: 0.875rem;
            color: rgb(var(--gray-500, 107 114 128));
        }

        .dark .bsf-subtitle {
            color: rgb(var(--gray-400, 156 163 175));
        }

        .bsf-trigger:focus-visible {
            outline: 2px solid rgb(var(--primary-500, 245 158 11));
            outline-offset: 1px;
        }
    </style>
@endonce

<x-dynamic-component
        :component="$fieldWrapperView"
        :field="$field"
        :has-inline-label="$hasInlineLabel"
        class="fi-fo-text-input-wrp"
>
    <div xmlns:x-filament="http://www.w3.org/1999/html"
         x-load-js="['https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js']"
         x-load-css="[@js(FilamentAsset::getStyleHref('barcode-scanner-field', 'marcelorodrigo/filament-barcode-scanner-field'))]"
         x-on:close-modal.window="stopScanning()"
         x-data="{
        scanner: null,
        status: 'idle',
        errorMessage: '',
        cameras: [],
        cameraIndex: 0,

        async stopScanning() {
            const scanner = this.scanner;
            this.scanner = null;
            this.status = 'idle';
            if (! scanner) {
                return;
            }
            try {
                if (scanner.isScanning) {
                    await scanner.stop();
                }
                scanner.clear();
            } catch (e) {}
        },

        openScannerModal() {
            $dispatch('open-modal', { id: '{{ $modalId }}' });
            this.$nextTick(() => setTimeout(() => this.startCamera(), 250));
        },

        closeScannerModal() {
            $dispatch('close-modal', { id: '{{ $modalId }}' });
        },

        async onScanSuccess(decodedText) {
            if (navigator.vibrate) {
                navigator.vibrate(120);
            }
            $wire.set('{{ $statePath }}', decodedText);
            await this.stopScanning();
            this.closeScannerModal();
        },

        fail(error) {
            const text = String((error && (error.name || error.message)) || error || '');
            let message = 'Não foi possível iniciar a câmera. Tente novamente.';

            if (! window.isSecureContext) {
                message = 'O acesso à câmera exige uma conexão segura (HTTPS).';
            } else if (/NotAllowed|Permission|denied/i.test(text)) {
                message = 'Permissão da câmera negada. Libere o acesso nas configurações do navegador e tente novamente.';
            } else if (/NotFound|no camera|Requested device not found/i.test(text)) {
                message = 'Nenhuma câmera foi encontrada neste dispositivo.';
            } else if (/NotReadable|in use|Could not start/i.test(text)) {
                message = 'A câmera está sendo usada por outro aplicativo. Feche-o e tente novamente.';
            }

            this.errorMessage = message;
            this.status = 'error';
        },

        async startCamera(cameraId = null) {
            if (typeof Html5Qrcode === 'undefined') {
                this.fail('O leitor ainda está carregando. Aguarde um instante e tente novamente.');
                this.errorMessage = 'O leitor ainda está carregando. Aguarde um instante e tente novamente.';
                return;
            }

            await this.stopScanning();
            this.status = 'starting';
            this.errorMessage = '';

            try {
                if (! this.cameras.length) {
                    this.cameras = await Html5Qrcode.getCameras();
                }

                const scanner = new Html5Qrcode('{{ $readerId }}', { verbose: false });
                this.scanner = scanner;

                await scanner.start(
                    cameraId ?? { facingMode: 'environment' },
                    {
                        fps: 10,
                        qrbox: (width, height) => {
                            const side = Math.floor(Math.min(width, height) * 0.8);
                            return { width: side, height: Math.floor(side * 0.65) };
                        },
                    },
                    (decodedText) => this.onScanSuccess(decodedText),
                    () => {}
                );

                this.status = 'scanning';
            } catch (error) {
                this.scanner = null;
                this.fail(error);
            }
        },

        switchCamera() {
            if (this.cameras.length < 2) {
                return;
            }
            this.cameraIndex = (this.cameraIndex + 1) % this.cameras.length;
            this.startCamera(this.cameras[this.cameraIndex].id);
        }
     }"
    >
        <div class="grid gap-y-2">
            <x-filament::input.wrapper :disabled="$isDisabled" :valid="! $errors->has($statePath)"
                                       :attributes="prepare_inherited_attributes($extraAttributeBag)->class(['fi-fo-text-input'])">
                <input {{ $inputAttributes->class(['fi-input']) }} />

                <x-slot name="suffix">
                    <button type="button" x-on:click="openScannerModal()"
                            @disabled($isDisabled)
                            class="bsf-trigger flex items-center justify-center w-9 h-9 -my-2 text-gray-400 dark:text-gray-200 hover:text-gray-500 dark:hover:text-gray-300 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                            title="Ler com a câmera"
                            aria-label="Ler código de barras ou QR Code com a câmera">
                        <x-dynamic-component :component="$getIcon()" class="fi-barcode-scanner-icon" />
                    </button>
                </x-slot>
            </x-filament::input.wrapper>
        </div>

        <!-- Modal do leitor de código -->
        <x-filament::modal id="{{ $modalId }}" width="lg" :close-by-clicking-away="false">
            <x-slot name="header">
                <h2 class="text-lg font-semibold">Escanear código</h2>
                <p class="bsf-subtitle">
                    Preenchendo o campo: {{ $getLabel() ?? 'código' }}
                </p>
            </x-slot>

            <div class="bsf-stage">
                <div id="{{ $readerId }}" class="bsf-reader"></div>

                <div class="bsf-overlay" x-show="status === 'starting'" x-cloak role="status">
                    <span class="bsf-spinner" aria-hidden="true"></span>
                    <span>Iniciando a câmera…</span>
                </div>

                <div class="bsf-overlay bsf-overlay--error" x-show="status === 'error'" x-cloak role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
                    </svg>
                    <span x-text="errorMessage"></span>
                    <button type="button" class="bsf-retry" x-on:click="startCamera()">Tentar novamente</button>
                </div>
            </div>

            <p class="bsf-hint" x-show="status === 'scanning'" x-cloak>
                Centralize o código de barras ou QR Code na área marcada. A leitura é automática.
            </p>

            <x-slot name="footer">
                <div class="flex w-full flex-wrap items-center justify-end gap-3">
                    <x-filament::button type="button" color="gray" outlined icon="heroicon-m-arrow-path"
                                        x-show="cameras.length > 1" x-cloak
                                        x-bind:disabled="status === 'starting'"
                                        @click="switchCamera()">
                        Trocar câmera
                    </x-filament::button>

                    <x-filament::button type="button" color="gray" @click="closeScannerModal()">
                        Cancelar
                    </x-filament::button>
                </div>
            </x-slot>
        </x-filament::modal>
    </div>
</x-dynamic-component>