<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class LanguageSwitcher extends Component
{
    public $currentLocale;
    public $availableLocales = ['en' => 'English', 'fa' => 'فارسی', 'ar' => 'العربية'];
    public $localeUrls = [];

    public function mount()
    {
        $this->currentLocale = Session::get('locale', config('app.locale'));
        $this->generateLocaleUrls();
    }

    private function generateLocaleUrls()
    {
        $path = request()->path(); // مسیر بدون domain
        $path = preg_replace('#^(fa|en|ar)/#', '', $path); // حذف prefix فعلی

        foreach ($this->availableLocales as $code => $label) {
            $this->localeUrls[$code] = '/' . $code . ($path ? '/' . $path : '');
        }
    }

    public function changeLocale($locale)
    {
        if (!array_key_exists($locale, $this->availableLocales)) return;

        Session::put('locale', $locale);
        App::setLocale($locale);

        $this->currentLocale = $locale;
        $this->generateLocaleUrls();

        // ریدایرکت به مسیر جدید با prefix درست
        return redirect($this->localeUrls[$locale]);
    }

    public function render()
    {
        return view('livewire.components.language-switcher', [
            'localeUrls' => $this->localeUrls,
            'currentLocale' => $this->currentLocale,
            'locales' => $this->availableLocales,
        ]);
    }
}
