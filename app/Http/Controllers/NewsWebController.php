<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\News;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Http\Request;

class NewsWebController extends Controller
{
    public $title;
    public $description;


    public function search(Request $request)
    {
        // منطق جستجو
        $query = $request->input('query'); // دریافت پارامتر جستجو
        // نمونه: جستجو در اخبار
        $results = News::where('title', 'like', "%{$query}%")->get();
        return view('search', compact('results')); // فرض بر این است که ویو search وجود دارد
    }

    public function category($slug)
    {
        // منطق نمایش دسته‌بندی
        $category = Category::where('slug', $slug)->firstOrFail();
        $news = $category->news()->paginate(66); // فرض بر این است که رابطه news تعریف شده است


        // بارگذاری عنوان و توضیحات از فایل ترجمه
        $this->title = __('menu.' . $category->slug . '.title');
        $this->description =  __('menu.' . $category->slug . '.description');

        // تنظیم تگ‌های SEO
        SEOMeta::setTitle($this->title);
        SEOMeta::setDescription($this->description);
        SEOMeta::setCanonical(request()->url());

        // تگ‌های OpenGraph
        OpenGraph::setTitle($this->title);
        OpenGraph::setDescription($this->description);
        OpenGraph::setUrl(request()->url());
        OpenGraph::addProperty('type', 'webpage');

        // تگ‌های Twitter Card
        SEOMeta::addMeta('twitter:card', 'summary_large_image');
        SEOMeta::addMeta('twitter:title', $this->title);
        SEOMeta::addMeta('twitter:description', $this->description);

        return view('category', compact('category', 'news')); // فرض بر این است که ویو category وجود دارد
    }

    public function childCategory($category_slug, $child_slug)
    {
        // منطق نمایش زیر-دسته‌بندی
        $category = Category::where('slug', $category_slug)->firstOrFail();
        $childCategory = Category::where('slug', $child_slug)->where('parent_id', $category->id)->firstOrFail();
        $news = $childCategory->news()->paginate(66); // فرض بر این است که رابطه news تعریف شده است
        // بارگذاری عنوان و توضیحات از فایل ترجمه
        $this->title = __('menu.' . $category->slug . '.title');
        $this->description =  __('menu.' . $category->slug . '.description');

        // تنظیم تگ‌های SEO
        SEOMeta::setTitle($this->title);
        SEOMeta::setDescription($this->description);
        SEOMeta::setCanonical(request()->url());

        // تگ‌های OpenGraph
        OpenGraph::setTitle($this->title);
        OpenGraph::setDescription($this->description);
        OpenGraph::setUrl(request()->url());
        OpenGraph::addProperty('type', 'webpage');

        // تگ‌های Twitter Card
        SEOMeta::addMeta('twitter:card', 'summary_large_image');
        SEOMeta::addMeta('twitter:title', $this->title);
        SEOMeta::addMeta('twitter:description', $this->description);
        return view('child_category', compact('category', 'childCategory', 'news')); // فرض بر این است که ویو child_category وجود دارد
    }

    public function news($slug)
    {
        // منطق نمایش جزئیات خبر
        $news = News::where('slug', $slug)->firstOrFail();
        // تنظیم تگ‌های SEO
        SEOMeta::setTitle($news->title);
        SEOMeta::setDescription($this->description);
        SEOMeta::setCanonical(request()->url());

        // تگ‌های OpenGraph
        OpenGraph::setTitle($news->title);
        OpenGraph::setDescription(mb_substr($news->description, 0, 120, 'UTF-8') . '...');
        OpenGraph::setUrl(request()->url());
        OpenGraph::addProperty('type', 'webpage');

        // تگ‌های Twitter Card
        SEOMeta::addMeta('twitter:card', 'summary_large_image');
        SEOMeta::addMeta('twitter:title', $news->title);
        SEOMeta::addMeta('twitter:description', mb_substr($news->description, 0, 120, 'UTF-8') . '...');
        return view('news', compact('news')); // فرض بر این است که ویو news وجود دارد
    }
}
