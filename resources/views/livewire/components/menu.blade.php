<div class="drawer-side z-50">
    <label for="my-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
    <ul class="menu bg-base-100 text-base-content min-h-full w-80 p-4 shadow-lg">
        @foreach ($menuItems as $key => $item)
            <li class="mb-2">
                @if (!empty($item['children']))
                    <details class="group">
                        <summary class="flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-colors duration-200">
                            <span class="w-5 h-5 group-open:rotate-90 transition-transform duration-200">
                               @if($item['icon'])
                                    <x-icon :name="$item['icon']" />
                               @endif
                            </span>
                            <span class="font-medium">{{ $item['title'] }}</span>
                        </summary>
                        <ul class="pl-6 pt-2 pb-1 space-y-1 transition-all duration-300 ease-in-out max-h-96 overflow-y-auto">
                            @foreach ($item['children'] as $child)
                                <li>
                                    <a href="{{ $child['slug'] === 'home' ? '/' : '/cat/' . ($child['slug'] === $key ? $key : $key . '/' . $child['slug']) }}"
                                       wire:click="$dispatch('close-drawer')"
                                       class="flex items-center gap-3 px-4 py-2 rounded-lg transition-colors duration-200">
                                        <span>{{ $child['title'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                @else
                    <a href="{{ $key === 'home' ? '/' : '/cat/' . $key }}"
                       wire:click="$dispatch('close-drawer')"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200">
                        <span class="w-5 h-5">
                            @if($item['icon'])
                                <x-icon :name="$item['icon']" />
                            @endif
                        </span>
                        <span class="font-medium">{{ $item['title'] }}</span>
                    </a>
                @endif
            </li>
        @endforeach
    </ul>
</div>
