<div class="drawer-side z-50">
    <label for="my-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
    <ul class="menu bg-base-100 text-base-content min-h-full w-80 p-4 shadow-lg">
        @foreach ($menuItems as $key => $item)
            <li class="mb-2">
                @if (!empty($item['children']))
                    <details class="group">
                        <summary class="flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-colors duration-200">
{{--                            <span class="w-5 h-5 text-red group-open:rotate-90 transition-transform duration-200">--}}
{{--                               @if($item['icon'])--}}
{{--                                  {{$item['icon']}}--}}

{{--                               @endif--}}
{{--                            </span>--}}
                            <span class="font-medium">{{ $item['title'] }}</span>
                        </summary>
                        <ul class="pl-6 pt-2 pb-1 space-y-1 transition-all duration-300 ease-in-out max-h-96 overflow-y-auto">
                            @foreach ($item['children'] as $child)
                                <li>
                                    <a href="{{ $child['slug'] === 'home' ? '/' : '/category/' . ($child['slug'] === $key ? $key : $key . '/' . $child['slug']) }}"
                                       wire:click="$dispatch('close-drawer')"
                                       class="flex items-center gap-3 px-4 py-2 rounded-lg transition-colors duration-200">
                                        <span>{{ $child['title'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                @else
                    <a href="{{ $key === 'home' ? '/' : '/category/' . $key }}"
                       wire:click="$dispatch('close-drawer')"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200">
                        <span class="w-5 h-5">
                            @switch($item['icon'])
                                @case('rss')
                                    <x-heroicon-o-rss />
                                    @break
                                @case('globe-alt')
                                    <x-heroicon-o-globe-alt />
                                    @break
                                @case('briefcase')
                                    <x-heroicon-o-briefcase />
                                    @break
                                @case('currency-dollar')
                                    <x-heroicon-o-currency-dollar />
                                    @break
                                @case('chip')
                                    <x-heroicon-o-cpu-chip />
                                    @break
                                @case('beaker')
                                    <x-heroicon-o-beaker />
                                    @break
                                @case('wallet')
                                    <x-heroicon-o-wallet />
                                    @break
                                @case('building-office')
                                    <x-heroicon-o-building-office />
                                    @break
                                @case('home')
                                    <x-heroicon-o-home />
                                    @break
                                @case('heart')
                                    <x-heroicon-o-heart />
                                    @break
                                @case('paint-brush')
                                    <x-heroicon-o-paint-brush />
                                    @break
                                @case('trophy')
                                    <x-heroicon-o-trophy />
                                    @break
                                @case('light-bulb')
                                    <x-heroicon-o-light-bulb />
                                    @break
                                @default
                                    <x-heroicon-o-circle-stack />
                            @endswitch
                        </span>
                        <span class="font-medium">{{ $item['title'] }}</span>
                    </a>
                @endif
            </li>
        @endforeach
    </ul>
</div>
