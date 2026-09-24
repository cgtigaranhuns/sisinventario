<x-filament-panels::page>
    <div class="mb-6 max-w-2xl">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            {{ $this->form }}

            <div
                x-data="{
                    ouvindo: false,
                    texto: '',
                    erro: null,
                    reconhecimento: null,
                    iniciar() {
                        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

                        if (! SpeechRecognition) {
                            this.erro = 'Seu navegador não suporta reconhecimento de voz.';
                            return;
                        }

                        if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                            this.erro = 'O microfone só funciona em HTTPS (ou localhost). Este site está em ' + location.protocol.replace(':', '') + '.';
                            return;
                        }

                        this.erro = null;
                        this.texto = '';
                        this.reconhecimento = new SpeechRecognition();
                        this.reconhecimento.lang = 'pt-BR';
                        this.reconhecimento.continuous = false;
                        this.reconhecimento.interimResults = false;
                        this.reconhecimento.maxAlternatives = 1;

                        this.reconhecimento.onstart = () => { this.ouvindo = true; };
                        this.reconhecimento.onerror = (event) => {
                            this.ouvindo = false;

                            const mensagens = {
                                'not-allowed': 'Permissão de microfone negada. Libere o microfone para este site nas configurações do navegador.',
                                'service-not-allowed': 'O navegador bloqueou o serviço de reconhecimento de voz (verifique se o site está em HTTPS).',
                                'audio-capture': 'Nenhum microfone foi encontrado neste dispositivo.',
                                'no-speech': 'Não ouvi nada. Tente falar novamente, mais perto do microfone.',
                                'network': 'Falha de rede ao contatar o serviço de reconhecimento de voz.',
                                'aborted': 'Reconhecimento cancelado.',
                            };

                            this.erro = mensagens[event.error] ?? ('Erro no reconhecimento de voz (' + event.error + ').');
                        };
                        this.reconhecimento.onend = () => { this.ouvindo = false; };
                        this.reconhecimento.onresult = (event) => {
                            const falado = event.results[0][0].transcript;
                            this.texto = falado;
                            $wire.buscarPorRpFalado(falado);
                        };

                        this.reconhecimento.start();
                    },
                }"
                class="mt-4 flex flex-wrap items-center gap-3 border-t border-gray-100 pt-4 dark:border-gray-800"
                style="margin-top:1rem;display:flex;flex-wrap:wrap;align-items:center;gap:0.6rem;border-top:1px solid rgba(148,163,184,0.25);padding-top:1rem;"
            >
                <button
                    type="button"
                    x-on:click="iniciar()"
                    :style="ouvindo ? 'color:#ef4444 !important;border-color:#ef4444 !important' : ''"
                    style="all:revert !important;display:inline-flex !important;flex-direction:row !important;flex-wrap:nowrap !important;align-items:center !important;justify-content:center !important;width:fit-content !important;max-width:max-content !important;white-space:nowrap !important;gap:0.4rem !important;background:transparent !important;border:1px solid #d1d5db !important;border-radius:9999px !important;padding:0.35rem 0.9rem !important;font-size:0.8125rem !important;font-weight:500 !important;color:#374151 !important;cursor:pointer !important;line-height:1.4 !important;box-sizing:border-box !important;"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block !important;width:14px !important;height:14px !important;flex:none !important;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3Z" />
                    </svg>
                    <span style="display:inline !important;">Falar código</span>
                </button>

                <span x-show="ouvindo" x-cloak style="font-size:0.8125rem;color:#6b7280;">Ouvindo...</span>
                <span x-show="texto" x-cloak x-text="'Você disse: ' + texto" style="font-size:0.8125rem;font-weight:500;"></span>
                <span x-show="erro" x-cloak x-text="erro" style="font-size:0.8125rem;color:#dc2626;"></span>
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>