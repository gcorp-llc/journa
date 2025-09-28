<x-filament-widgets::widget>
    <x-filament::section>
        <x-filament::widget>
            <x-filament::card>
                <div class="flex flex-col items-center justify-center">
                    <h2 class="text-lg font-bold">اجرای دستور کراولر اخبار</h2>
                    <button
                        wire:click="runCommand"
                        class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                    >
                        اجرای دستور
                    </button>
                </div>
            </x-filament::card>
        </x-filament::widget>
    </x-filament::section>
</x-filament-widgets::widget>
