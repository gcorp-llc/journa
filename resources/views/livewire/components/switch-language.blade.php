<?php

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component {

    public $englishUrl, $arabicUrl, $persianUrl;

    public function mount()
    {
        $url = str_replace(asset(''), '', url()->current());
        $url = ltrim($url, '/');

        $hasLocale = Str::startsWith($url, ['fa/', 'en/', 'ar/']);
        $cleanUrl = $hasLocale ? preg_replace('#^(fa|en|ar)/#', '', $url) : $url;

        $this->persianUrl = '/fa' . ($cleanUrl ? '/' . $cleanUrl : '');
        $this->englishUrl = '/en' . ($cleanUrl ? '/' . $cleanUrl : '');
        $this->arabicUrl = '/ar' . ($cleanUrl ? '/' . $cleanUrl : '');

        if (Route::is('home')) {
            $this->persianUrl = '/fa';
            $this->englishUrl = '/en';
            $this->arabicUrl = '/ar';
        }
    }

    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }

}; ?>

@php
    $localeUrls = [
        'fa' => $persianUrl,
        'en' => $englishUrl,
        'ar' => $arabicUrl,
    ];
    $locales = ['fa' => 'فارسی', 'en' => 'English', 'ar' => 'العربی'];
@endphp

<div class="dropdown dropdown-end hidden lg:block">
    <div tabindex="0" role="button" class="btn btn-sm btn-success btn-circle">
        {{ strtoupper(app()->getLocale()) }}
    </div>
    <ul tabindex="0" class="dropdown-content menu bg-base-200 rounded-box z-[1] w-40 p-2 shadow-lg mt-2">
        @foreach($locales as $code => $label)
            @if(app()->getLocale() !== $code)
                <li>
                    <a href="{{ asset($localeUrls[$code]) }}"
                       class="block px-4 py-2 hover:bg-base-100 rounded-lg transition-colors">
                        {{ $label }}
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</div>
