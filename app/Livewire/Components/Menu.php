<?php

namespace App\Livewire\Components;

use App\Models\Category;
use Illuminate\Support\Facades\Request;
use Livewire\Component;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class Menu extends Component
{
    public $menuItems;
    public $currentCategory;

    public function mount()
    {
        // دریافت زبان فعلی
        $locale = app()->getLocale();

        // دریافت دسته‌بندی فعلی از URL (بدون پیشوند زبان)
        $this->currentCategory = preg_replace('/^(fa|en|ar)\//', '', Request::path());

        // دریافت دسته‌بندی‌های اصلی با فرزندان، مرتب‌شده بر اساس sort_order
        $categories = Category::whereNull('parent_id')
            ->orderByRaw('slug = "home" DESC') // اطمینان از اینکه home اولین باشد
            ->orderBy('sort_order', 'asc')
            ->with(['children' => function ($query) {
                $query->orderBy('sort_order', 'asc');
            }])
            ->get();

        // ساخت آیتم‌های منو
        $this->menuItems = [];
        foreach ($categories as $category) {
            $children = [];
            // اگر دسته‌بندی والد زیرمجموعه دارد، خودش را به‌عنوان اولین گزینه اضافه کن
            if ($category->children->isNotEmpty()) {
                $children[] = [
                    'slug' => $category->slug,
                    'title' => $category->title,
                    'icon' => null, // آیکون برای زیرمجموعه‌ها غیرفعال است
                ];
            }
            // اضافه کردن زیرمجموعه‌ها
            foreach ($category->children as $child) {
                $children[] = [
                    'slug' => $child->slug,
                    'title' => $child->title,
                    'icon' => null, // آیکون برای زیرمجموعه‌ها غیرفعال است
                ];
            }

            $this->menuItems[$category->slug] = [
                'title' => $category->title,
                'icon' => $category->icon, // آیکون فقط برای دسته‌بندی اصلی
                'children' => $children,
            ];
        }
    }

    public function render()
    {
        return view('livewire.components.menu');
    }
}
