<header class="fixed top-0 left-0 right-0 z-50 font-vazir">
    <div class="flex justify-between items-center p-2 bg-amber-500 bg-opacity-95 backdrop-blur-sm rounded-xl shadow-lg m-2">
        <!-- Drawer -->
        <div class="flex items-center gap-4">
            <div class="drawer drawer-start">
                <input id="my-drawer" type="checkbox" class="drawer-toggle" />
                <div class="drawer-content">
                    <label for="my-drawer" class="btn btn-ghost btn-circle hover:bg-amber-600">
                        <span class="icon-[si--grid-view-fill] w-6 h-6 text-white" role="img" aria-hidden="true"></span>
                    </label>
                </div>
                <livewire:components.menu/>
            </div>
        </div>

        <!-- Search, Language Switcher, Logo -->
        <div class="flex items-center gap-1 md:gap-4">
            <a href="/{{ session('locale', app()->getLocale()) }}/search"
               class="btn btn-ghost btn-circle hover:bg-amber-600">
                <div class="w-6 h-6 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </a>

            <!-- Language Switcher -->
            <livewire:components.switch-language/>

            <!-- Logo -->
            <a href="/{{ session('locale', app()->getLocale()) }}"
               class="w-9 hover:opacity-90 transition-opacity">
                <img src="{{ asset('favicon.png') }}"
                     alt="Journa News"
                     width="50"
                     height="50"
                     class="rounded-full"/>
            </a>
        </div>
    </div>
</header>
