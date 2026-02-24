<x-filament-panels::page>
    <div class="mx-auto w-full max-w-2xl">
        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <div class="flex items-center justify-end gap-3">
                <x-filament::button type="submit" size="lg">
                    Update Password
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>