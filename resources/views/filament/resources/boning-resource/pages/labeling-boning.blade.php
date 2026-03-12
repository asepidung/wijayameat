<x-filament-panels::page>
    <style>
        /* Cuma umpetin judul halaman bawaan, JANGAN ganggu main-container biar navbar aman */
        .fi-header {
            display: none !important;
        }
    </style>

    <div class="mb-6 flex items-center justify-between rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <x-filament::button
            href="{{ \App\Filament\Resources\BoningResource::getUrl('index') }}"
            tag="a"
            color="gray"
            icon="heroicon-m-arrow-left">
            DATA BONING
        </x-filament::button>

        <div class="text-lg font-bold uppercase tracking-wider text-gray-950 dark:text-white">
            BATCH: <span class="text-primary-600 dark:text-primary-400">{{ $record->doc_no }}</span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 32% 1fr; gap: 1.5rem; align-items: start; width: 100%;">

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10" style="position: sticky; top: 1.5rem;">
            <form wire:submit.prevent="create">

                {{ $this->form }}

                <div class="mt-6 w-full">
                    <x-filament::button
                        type="submit"
                        size="xl"
                        class="w-full">
                        PRINT & SAVE LABEL
                    </x-filament::button>
                </div>

            </form>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            {{ $this->table }}
        </div>

    </div>
</x-filament-panels::page>