<?php

namespace App\Jobs;

use App\Traits\InteractsWithHttp;
use App\Services\TranslationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Str;

class CrawlNewsContentJob implements ShouldQueue
{
    use Queueable, InteractsWithHttp;

    private const MAX_RETRIES = 3;
    private const RETRY_DELAYS = [30, 60, 120]; // ثانیه

    public $tries = self::MAX_RETRIES;
    public $backoff = self::RETRY_DELAYS;

    public function __construct(
        private readonly string $siteName,
        private readonly int $siteId,
        private readonly int $parentCategoryId,
        private readonly string $url,
        private readonly array $newsSelectors = [],
    ) {}

    public function handle(TranslationService $translationService): void
    {
        $jobId = uniqid('content_', true);

        try {
            Log::info('🔍 شروع پردازش محتوای خبر', [
                'url' => $this->url,
                'site_id' => $this->siteId,
                'job_id' => $jobId,
            ]);

            $response = $this->sendRequest($this->url, 'get', ['job_id' => $jobId]);
            $html = $response->body();

            if (strlen($html) < 500) {
                throw new \Exception('صفحه خالی یا ناقص دریافت شد');
            }

            $crawler = new Crawler($html);
            $jsonLd = $this->extractJsonLdData($crawler);

            // استخراج عنوان
            $title = $jsonLd['headline']
                ?? $this->extractBySelector($crawler, 'title')
                ?? ($crawler->filter('title')->count() ? trim($crawler->filter('title')->text()) : null);

            if (empty($title)) {
                throw new \Exception('عنوان خبر یافت نشد');
            }

            // استخراج و تمیزسازی محتوا
            $rawContent = $this->extractRawContent($crawler);
            $cleanedContent = $this->cleanHtmlContent($rawContent);

            if (empty($cleanedContent) && !empty($jsonLd['description'])) {
                $cleanedContent = '<p>' . $jsonLd['description'] . '</p>';
            }

            if (empty($cleanedContent) || strlen(strip_tags($cleanedContent)) < 80) {
                throw new \Exception('محتوای خبر کوتاه یا نامعتبر است');
            }

            // ترجمه
            $translations = $translationService->translateArray(
                ['title' => $title, 'content' => $cleanedContent],
                ['title', 'content']
            );

            // ذخیره خبر
            $newsId = $this->saveNews($translations);

            // پردازش دسته‌بندی‌ها
            $this->processCategories($newsId, $crawler);

            // استخراج لینک تصویر کاور
            $coverImageUrl = $this->extractCoverImageUrl($crawler, $jsonLd['image'] ?? null);
            $slugForImage = $translations['title']['en'] ?? Str::slug(Str::limit($title, 50));

            if ($coverImageUrl) {
                ProcessNewsImageJob::dispatch($newsId, $this->siteName, $coverImageUrl, $slugForImage)
                    ->onQueue('images');
            } else {
                Log::warning('⚠️ تصویر کاور یافت نشد', ['news_id' => $newsId, 'url' => $this->url]);
            }

            Log::info('✅ خبر با موفقیت پردازش و ذخیره شد', [
                'news_id' => $newsId,
                'title' => Str::limit($title, 50),
            ]);

        } catch (\Exception $e) {
            Log::error('❌ خطا در پردازش محتوای خبر', [
                'url' => $this->url,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'job_id' => $jobId,
            ]);

            $this->fail($e);
        }
    }

    /**
     * استخراج هوشمند و دقیق تصویر کاور با پشتیبانی بهتر از srcset
     */
    private function extractCoverImageUrl(Crawler $crawler, mixed $jsonLdImage): ?string
    {
        // ۱. اولویت اول: JSON-LD (اگر آرایه بود، اولین عنصر را بردار)
        if (!empty($jsonLdImage)) {
            $imgUrl = is_array($jsonLdImage) ? ($jsonLdImage['url'] ?? $jsonLdImage[0] ?? null) : $jsonLdImage;
            if ($imgUrl && is_string($imgUrl) && filter_var($imgUrl, FILTER_VALIDATE_URL)) {
                return $this->normalizeUrl($imgUrl);
            }
        }

        // ۲. متاتگ‌های استاندارد
        $metaSelectors = [
            'meta[property="og:image"]' => 'content',
            'meta[name="twitter:image"]' => 'content',
            'meta[property="twitter:image"]' => 'content',
            'link[rel="image_src"]' => 'href',
        ];

        // اضافه کردن سلکتور خاص سایت از کانفیگ
        if (!empty($this->newsSelectors['cover_alt'])) {
            $key = $this->newsSelectors['cover_alt'];
            $attr = (str_contains($key, 'meta') || str_contains($key, 'og:')) ? 'content' : 'src';
            $metaSelectors[$key] = $attr;
        }

        foreach ($metaSelectors as $selector => $attr) {
            try {
                if ($crawler->filter($selector)->count() > 0) {
                    $url = $crawler->filter($selector)->attr($attr);
                    if ($url) return $this->normalizeUrl($url);
                }
            } catch (\Exception) { continue; }
        }

        // ۳. جستجو در بدنه (CSS Selectors) با پشتیبانی از srcset
        $cssKeys = ['cover', 'cover_carousel', 'featured_image', 'main_image'];
        foreach ($cssKeys as $key) {
            if (empty($this->newsSelectors[$key])) continue;

            try {
                $nodes = $crawler->filter($this->newsSelectors[$key]);
                if ($nodes->count() === 0) continue;

                $node = $nodes->first(); // اولین مورد پیدا شده

                // بررسی برای srcset (معمولاً تصاویر با کیفیت اینجا هستند)
                $srcset = $node->attr('srcset') ?? $node->attr('data-srcset');
                if ($srcset) {
                    $bestImage = $this->parseSrcset($srcset);
                    if ($bestImage) return $this->normalizeUrl($bestImage);
                }

                // بررسی ویژگی‌های مختلف سورس
                $src = $node->attr('src')
                    ?? $node->attr('data-src')
                    ?? $node->attr('data-original')
                    ?? $node->attr('data-lazy-src');

                if ($src) return $this->normalizeUrl($src);

            } catch (\Exception) { continue; }
        }

        return null;
    }

    /**
     * پارس کردن srcset برای پیدا کردن بزرگترین تصویر
     */
    private function parseSrcset(string $srcset): ?string
    {
        $candidates = explode(',', $srcset);
        $bestUrl = null;
        $maxWidth = 0;

        foreach ($candidates as $candidate) {
            $parts = preg_split('/\s+/', trim($candidate), -1, PREG_SPLIT_NO_EMPTY);
            if (count($parts) === 0) continue;

            $url = $parts[0];
            $width = 0;

            if (isset($parts[1]) && str_ends_with($parts[1], 'w')) {
                $width = (int) rtrim($parts[1], 'w');
            }

            if ($width > $maxWidth) {
                $maxWidth = $width;
                $bestUrl = $url;
            }
        }

        // اگر هیچ عرضی مشخص نشده بود، اولین مورد را برگردان
        return $bestUrl ?? explode(' ', trim($candidates[0]))[0];
    }

    private function normalizeUrl(string $link): string
    {
        $link = trim($link);
        if (str_starts_with($link, 'http')) {
            return $link;
        }

        $parsed = parse_url($this->url);
        $base = $parsed['scheme'] . '://' . $parsed['host'];

        return str_starts_with($link, '/') ? $base . $link : $base . '/' . $link;
    }

    /**
     * استخراج HTML خام قبل از تمیزکاری
     */
    private function extractRawContent(Crawler $crawler): string
    {
        // حذف نویزهای اولیه بر اساس سلکتورهای کانفیگ
        $unwanted = array_merge([
            'script', 'style', 'iframe', 'nav', 'header', 'footer', 'form',
            '.ads', '.advertisement', '.social-share', '.related-posts',
            '[class*="share-"]', '[class*="social-"]', '[id*="ad-"]'
        ], $this->newsSelectors['unwanted_content_selectors'] ?? []);

        foreach ($unwanted as $selector) {
            try {
                $crawler->filter($selector)->each(fn($node) =>
                $node->getNode(0)->parentNode ? $node->getNode(0)->parentNode->removeChild($node->getNode(0)) : null
                );
            } catch (\Exception) {
                // نادیده گرفتن خطاهای DOM
            }
        }

        $contentSelector = $this->newsSelectors['content'] ?? 'article';
        $html = '';

        try {
            $crawler->filter($contentSelector)->each(function (Crawler $node) use (&$html) {
                // استفاده از innerHtml برای جلوگیری از تکرار تگ والد اگر نیازی نیست،
                // اما outerHtml امن‌تر است برای حفظ ساختار
                $html .= '<div>' . $node->outerHtml() . '</div>';
            });
        } catch (\Exception) {
            // فال‌بک
        }

        return $html ?: '';
    }

    /**
     * تمیزکاری پیشرفته HTML با DOMDocument
     * حذف کلاس‌ها، استایل‌ها و لینک‌های مزاحم
     */
    private function cleanHtmlContent(string $html): string
    {
        if (empty($html)) return '';

        // استفاده از DOMDocument برای دستکاری ساختار
        $dom = new \DOMDocument();
        // جلوگیری از خطاهای پارس HTML5 و تنظیم انکودینگ UTF-8
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // ۱. حذف تگ‌های <a> که لینک‌های مزاحم (مثل x.com) هستند
        // اگر لینک به توییتر/ایکس یا فیسبوک است، کل نود را حذف کن (چون معمولا دکمه share یا امبد است)
        $socialLinks = $xpath->query('//a[contains(@href, "x.com") or contains(@href, "twitter.com") or contains(@href, "facebook.com") or contains(@href, "linkedin.com")]');
        foreach ($socialLinks as $node) {
            $node->parentNode->removeChild($node);
        }

        // ۲. حذف تمام اتریبیوت‌ها (class, style, id, ...) به جز src و href معتبر
        $allNodes = $xpath->query('//*');
        foreach ($allNodes as $node) {
            // لیست اتریبیوت‌هایی که باید بمانند
            $allowedAttributes = ['src', 'alt', 'title'];
            // اگر تگ a است، href را نگه دار (مگر اینکه بخواهید تمام لینک‌ها را غیرفعال کنید)
            if ($node->nodeName === 'a') {
                $allowedAttributes[] = 'href';
            }

            if ($node->hasAttributes()) {
                // کپی اتریبیوت‌ها به آرایه برای حذف امن در حلقه
                $attributesToRemove = [];
                foreach ($node->attributes as $attr) {
                    if (!in_array($attr->name, $allowedAttributes)) {
                        $attributesToRemove[] = $attr->name;
                    }
                }
                foreach ($attributesToRemove as $attrName) {
                    $node->removeAttribute($attrName);
                }
            }
        }

        // ۳. حذف تگ‌های خالی (مثل <p></p> که محتوایش حذف شده)
        // چندین بار اجرا می‌شود تا تگ‌های تو در تو خالی حذف شوند
        do {
            $emptyNodes = $xpath->query('//*[not(*) and not(normalize-space()) and not(@src)]');
            $removed = 0;
            foreach ($emptyNodes as $node) {
                // تگ‌های br و img نباید حذف شوند
                if (!in_array($node->nodeName, ['br', 'img', 'hr'])) {
                    $node->parentNode->removeChild($node);
                    $removed++;
                }
            }
        } while ($removed > 0);

        return trim($dom->saveHTML());
    }

    private function processCategories(int $newsId, Crawler $crawler): void
    {
        $categoryIds = [$this->parentCategoryId];

        $selector = $this->newsSelectors['breadcrumb'] ?? $this->newsSelectors['category'] ?? null;
        if ($selector) {
            try {
                if ($crawler->filter($selector)->count() > 0) {
                    $text = trim($crawler->filter($selector)->last()->text());
                    $detectedId = DB::table('news_site_categories')
                        ->where('news_site_id', $this->siteId)
                        ->where('title', $text)
                        ->value('id');

                    if ($detectedId) $categoryIds[] = $detectedId;
                }
            } catch (\Exception) {}
        }

        $categoryIds = array_unique($categoryIds);
        $insertData = array_map(fn($catId) => [
            'news_id' => $newsId,
            'category_id' => $catId,
            'created_at' => now(),
            'updated_at' => now(),
        ], $categoryIds);

        DB::table('category_news')->insertOrIgnore($insertData);
    }

    private function saveNews(array $translations): int
    {
        return DB::transaction(function () use ($translations) {
            $englishTitle = $translations['title']['en'] ?? 'news-' . uniqid();
            $slug = Str::slug(Str::limit($englishTitle, 100)); // محدودیت طول اسلاگ

            $originalSlug = $slug;
            $counter = 1;
            while (DB::table('news')->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }

            DB::table('news')->updateOrInsert(
                ['source_url' => $this->url],
                [
                    'title' => json_encode($translations['title'], JSON_UNESCAPED_UNICODE),
                    'content' => json_encode($translations['content'], JSON_UNESCAPED_UNICODE),
                    'slug' => $slug,
                    'news_site_id' => $this->siteId,
                    'status' => 'published',
                    'published_at' => now(),
                    'updated_at' => now(),
                ]
            );

            return DB::table('news')->where('source_url', $this->url)->value('id');
        });
    }

    private function extractJsonLdData(Crawler $crawler): array
    {
        try {
            $scripts = $crawler->filter('script[type="application/ld+json"]');
            foreach ($scripts as $script) {
                $content = trim($script->textContent);
                if (empty($content)) continue;

                $data = json_decode($content, true);
                // هندل کردن حالتی که json-ld آرایه‌ای از آبجکت‌هاست (Graph)
                if (isset($data['@graph'])) {
                    foreach ($data['@graph'] as $item) {
                        if (isset($item['@type']) && in_array($item['@type'], ['NewsArticle', 'Article', 'BlogPosting'])) {
                            return $item;
                        }
                    }
                }

                if (is_array($data) && isset($data['@type']) && in_array($data['@type'], ['NewsArticle', 'Article', 'BlogPosting'])) {
                    return $data;
                }
            }
        } catch (\Exception) {}
        return [];
    }

    private function extractBySelector(Crawler $crawler, string $key): ?string
    {
        if (empty($this->newsSelectors[$key])) return null;
        try {
            return trim($crawler->filter($this->newsSelectors[$key])->text());
        } catch (\Exception) { return null; }
    }
}
