<div class="dropdown dropdown-end">
    <div tabindex="0" role="button" class="btn btn-sm btn-ghost btn-circle hover:bg-amber-600 flex items-center gap-1">
        <x-heroicon-o-language class="w-6 h-6 text-white"/>
        <span class="text-white">{{ strtoupper($currentLocale) }}</span>
    </div>

    <ul tabindex="0" class="dropdown-content menu bg-base-200 rounded-box z-[1] w-40 p-2 shadow-lg mt-2">
        @foreach($locales as $code => $label)
            <li>
                <a href="#"
                   wire:click.prevent="changeLocale('{{ $code }}')"
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ $currentLocale === $code ? 'bg-blue-400 text-white' : '' }}">
                    {{ $label }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
