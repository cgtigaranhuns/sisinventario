<x-filament-panels::page>
    <div class="mb-6 max-w-2xl">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            {{ $this->form }}
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>