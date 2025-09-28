<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\App;
use Livewire\Component;

class Header extends Component
{
    public $currentLocale;
    public $locales = ['fa' => 'فارسی', 'en' => 'English', 'ar' => 'العربية'];
    public $localeUrls = [];

    public function mount()
    {
        // مقداردهی اولیه زبان از session یا app default
        $this->currentLocale = \Session::get('locale', config('app.locale'));
        $this->generateLocaleUrls();
    }

    private function generateLocaleUrls()
    {
        // مسیر فعلی بدون prefix زبان
        $path = request()->path();
        $segments = explode('/', $path);
        if (in_array($segments[0], array_keys($this->locales))) {
            array_shift($segments);
        }
        $basePath = implode('/', $segments);

        foreach ($this->locales as $code => $label) {
            $this->localeUrls[$code] = '/' . $code . ($basePath ? '/' . $basePath : '');
        }
    }

    public function changeLocale($locale)
    {
        if (!array_key_exists($locale, $this->locales)) return;

        // آپدیت Session و App locale
        \Session::put('locale', $locale);
        App::setLocale($locale);
        $this->currentLocale = $locale;

        // بازسازی URL برای تمام زبان‌ها
        $this->generateLocaleUrls();

        // ریدایرکت به URL جدید
        return redirect($this->localeUrls[$locale]);
    }

    public function render()
    {
        return view('livewire.components.header', [
            'localeUrls' => $this->localeUrls,
            'currentLocale' => $this->currentLocale,
            'locales' => $this->locales,
        ]);
    }
}
