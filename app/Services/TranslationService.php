<?php

namespace App\Services;

use App\Enums\Locale;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Throwable;

class TranslationService
{
    // ثوابت (Constants)
    private const CACHE_PREFIX = 'translation';
    private const SPELLING_CACHE_PREFIX = 'spelling_correction';

    /**
     * CHUNK_SIZE به یک مقدار بهینه (مانند 4000) تنظیم شد.
     * منطق تقسیم به جملات درون تابع ترجمه مدیریت می‌شود.
     */
    private const CHUNK_SIZE = 3000;

    private const CACHE_TTL_HOURS = 168; // 1 هفته کشینگ
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY_SECONDS = 2;

    /**
     * ترجمه متن به همه زبان‌های تعریف شده در `Locale` enum.
     *
     * @param string $text متن ورودی برای ترجمه
     * @param string|null $sourceLang زبان مبدا (اگر null باشد، توسط GoogleTranslate تشخیص داده می‌شود)
     * @param bool $correctSpelling آیا اصلاح املایی (با استفاده از Back-translation) انجام شود؟ (به صورت پیش‌فرض خاموش برای سرعت)
     * @return array<string, string> آرایه‌ای از ترجمه‌ها با کلید زبان
     */
    public function translateToAll(string $text, ?string $sourceLang = null, bool $correctSpelling = false): array
    {
        $text = trim($text);

        if (empty($text)) {
            return $this->getEmptyTranslationsArray();
        }

        // نکته مهم: اگر $sourceLang null باشد، آن را null می‌گذاریم تا
        // کتابخانه GoogleTranslate (که دقت تشخیص بالاتری دارد) خودش زبان را تشخیص دهد.
        // تشخیص زبان داخلی (detectLanguage) به دلیل غیردقیق بودن حذف شد.

        $originalText = $text;

        // اصلاح املایی (Spelling Correction) - به صورت پیش‌فرض خاموش
        if ($correctSpelling && $sourceLang !== Locale::EN->value) {
            $text = $this->correctSpelling($text, $sourceLang) ?? $text;
        }

        // استفاده از زبان مبدأ تشخیص داده شده یا فرض شده در خروجی
        $translations = [$sourceLang ?? 'auto' => $text];

        // --- پیشنهاد بهینه‌سازی سرعت: ترجمه موازی ---
        // این حلقه به صورت ترتیبی (Sequential) اجرا می‌شود که کندترین بخش کد است.
        // برای بهینه‌سازی سرعت، این کار باید به صورت موازی با استفاده از Laravel Queues یا HTTP Client Promises انجام شود.
        // فعلاً ساختار ترتیبی حفظ شده است.
        // ----------------------------------------------

        foreach (Locale::cases() as $targetLocale) {
            $targetLang = $targetLocale->value;

            // اگر زبان مبدأ مشخص نشده بود، ترجمه به همان زبان مقصد معنی ندارد.
            if ($targetLang === ($sourceLang ?? '')) {
                continue;
            }

            $translated = $this->translate($text, $targetLang, $sourceLang);

            // اگر ترجمه شکست خورد یا عین متن اصلی بود، از مسیر Fallback استفاده کن.
            if ($this->isTranslationFailed($translated, $text)) {
                Log::warning('Translation failed, attempting fallback', [
                    'source' => $sourceLang ?? 'auto',
                    'target' => $targetLang
                ]);
                $translated = $this->translateWithFallback($text, $targetLang, $sourceLang);
            }

            // اطمینان از اینکه خروجی Fallback باز هم متن ورودی نیست (در صورت امکان)
            $translations[$targetLang] = $this->isTranslationFailed($translated, $text) ? $originalText : $translated;
        }

        return $translations;
    }

    /**
     * فیلدهای مشخص‌شده از یک آرایه را ترجمه می‌کند.
     */
    public function translateArray(array $data, array $fields): array
    {
        $translatedData = [];

        foreach ($fields as $field) {
            // توجه: در اینجا اصلاح املایی به صورت پیش‌فرض خاموش است.
            $translatedData[$field] = isset($data[$field]) && !empty(trim($data[$field]))
                ? $this->translateToAll($data[$field])
                : $this->getEmptyTranslationsArray();
        }

        return $translatedData;
    }

    /**
     * این متد حذف شد زیرا تشخیص زبان دقیق‌تر توسط کتابخانه Google Translate انجام می‌شود.
     * در صورت نیاز به تشخیص زبان در خارج از این سرویس، از یک سرویس یا پکیج اختصاصی (مانند lang-detect) استفاده کنید.
     */
    public function detectLanguage(string $text): string
    {
        return 'en'; // فقط برای حفظ امضای متد اصلی و عدم شکستن کد. منطق داخلی حذف شد.
    }

    /**
     * ترجمه یک متن با استفاده از Google Translate.
     * این متد متن‌های طولانی را به قطعات کوچک‌تر تقسیم می‌کند (با حفظ جملات).
     */
    private function translate(string $text, string $targetLang, ?string $sourceLang): string
    {
        $chunkSize = self::CHUNK_SIZE;

        if (mb_strlen($text) <= $chunkSize) {
            return $this->translateChunk($text, $targetLang, $sourceLang);
        }

        // برای متن‌های طولانی، از منطق ترجمه مبتنی بر جمله استفاده می‌کنیم.
        return $this->translateLongTextBySentence($text, $targetLang, $sourceLang, $chunkSize);
    }

    /**
     * ترجمه متن‌های طولانی با تقسیم به chunks (بر اساس جملات)
     * این متد جایگزین translateLongText و translateGoogleWithSentenceSplitting شد.
     */
    private function translateLongTextBySentence(string $text, string $targetLang, ?string $sourceLang, int $chunkSize): string
    {
        // 1. تقسیم به جملات و گروه‌بندی به chunks (حفظ مرز جملات)
        $sentences = $this->splitIntoSentences($text);
        $chunks = $this->groupSentencesIntoChunks($sentences, $chunkSize);
        $translatedChunks = [];

        Log::info('Translating chunked text by sentence', [
            'chunks' => count($chunks),
            'source' => $sourceLang ?? 'auto',
            'target' => $targetLang
        ]);

        foreach ($chunks as $chunk) {
            // استفاده از translateChunk برای بهره‌مندی از Cache
            $translated = $this->translateChunk($chunk, $targetLang, $sourceLang);

            // در صورت شکست ترجمه، از متن اصلی استفاده کن تا جمله از دست نرود.
            $translatedChunks[] = $this->isTranslationFailed($translated, $chunk) ? $chunk : $translated;
        }

        // از implode خالی استفاده می‌کنیم تا به نقطه‌گذاری اصلی (که در جملات وجود دارد) احترام بگذاریم.
        return implode(' ', $translatedChunks);
    }

    /**
     * یک قطعه از متن را با استفاده از کش و Google Translate ترجمه می‌کند.
     */
    private function translateChunk(string $text, string $targetLang, ?string $sourceLang): string
    {
        $cacheKey = $this->generateCacheKey($text, $sourceLang ?? 'auto', $targetLang);
        $cacheTtl = self::CACHE_TTL_HOURS;

        return Cache::remember(
            $cacheKey,
            now()->addHours($cacheTtl),
            fn() => $this->translateGoogleSingleChunk($text, $targetLang, $sourceLang)
        );
    }

    /**
     * ترجمه یک chunk کوچک با Google با retry.
     */
    private function translateGoogleSingleChunk(
        string $chunk,
        string $targetLang,
        ?string $sourceLang,
        ?int $maxRetries = null
    ): string {
        $maxRetries = $maxRetries ?? self::MAX_RETRIES;
        $retryDelay = self::RETRY_DELAY_SECONDS;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                // Instantiation در اینجا باقی می‌ماند تا تضمین کند در هر تلاش، شیء تازه است.
                $translator = new GoogleTranslate($targetLang, $sourceLang, ['timeout' => 30]);
                $result = $translator->translate($chunk);

                // از متد isValidTranslation استفاده می‌کنیم.
                if ($this->isValidTranslation($result, $chunk)) {
                    return trim($result);
                }
            } catch (Throwable $e) {
                Log::warning("Google Translate failed", [
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                    'error' => $e->getMessage(),
                    'source' => $sourceLang ?? 'auto',
                    'target' => $targetLang
                ]);

                if ($attempt < $maxRetries) {
                    sleep($retryDelay);
                }
            }
        }

        return ''; // برگرداندن رشته خالی پس از تلاش‌های مکرر ناموفق
    }

    /**
     * اصلاح اشتباهات املایی با ترجمه به انگلیسی و برگرداندن به زبان اصلی.
     * توجه: این فرآیند دو تماس API دارد و سرعت را به شدت کاهش می‌دهد.
     */
    private function correctSpelling(string $text, ?string $sourceLang): ?string
    {
        if (empty($sourceLang) || $sourceLang === Locale::EN->value) {
            return null;
        }

        $cacheKey = $this->generateSpellingCacheKey($text, $sourceLang);
        $cacheTtl = self::CACHE_TTL_HOURS;

        return Cache::remember($cacheKey, now()->addHours($cacheTtl), function () use ($text, $sourceLang) {
            // 1. ترجمه به انگلیسی
            // از translateGoogleSingleChunk استفاده می‌کنیم تا از Retry و Timeout بهره‌مند شویم.
            $toEnglish = $this->translateGoogleSingleChunk($text, Locale::EN->value, $sourceLang);

            if (empty($toEnglish) || $this->isTranslationFailed($toEnglish, $text)) {
                return null;
            }

            // 2. ترجمه بازگشتی به زبان مبدأ
            $backToSource = $this->translateGoogleSingleChunk($toEnglish, $sourceLang, Locale::EN->value);

            // اگر ترجمه برگشتی معتبر و متفاوت از متن اصلی بود، آن را برگردان.
            if ($this->isValidTranslation($backToSource, $text)) {
                return $backToSource;
            }

            return null;
        });
    }

    /**
     * ترجمه با استفاده از مسیر fallback (از طریق انگلیسی)
     */
    private function translateWithFallback(string $text, string $targetLang, ?string $sourceLang): string
    {
        // 1. ترجمه به انگلیسی (از Cache و Chunking بهره می‌برد)
        $intermediate = $this->translate($text, Locale::EN->value, $sourceLang);

        // اگر ترجمه به انگلیسی معتبر نبود یا عین متن اصلی بود، Fallback معنی ندارد.
        if (!$this->isValidTranslation($intermediate, $text)) {
            return $text;
        }

        // 2. ترجمه از انگلیسی به زبان مقصد
        $final = $this->translate($intermediate, $targetLang, Locale::EN->value);

        // اگر ترجمه نهایی موفقیت‌آمیز نبود، متن اصلی را برگردان.
        return $this->isTranslationFailed($final, $intermediate) ? $text : $final;
    }

    /**
     * بررسی اینکه آیا ترجمه شکست خورده است (ترجمه خالی است یا عین متن اصلی)
     */
    private function isTranslationFailed(?string $translated, string $original): bool
    {
        // استفاده از strcasecmp برای مقایسه بدون حساسیت به حروف بزرگ و کوچک
        return empty($translated) || trim($translated) === '' || strcasecmp(trim($translated), trim($original)) === 0;
    }

    /**
     * بررسی معتبر بودن ترجمه
     */
    private function isValidTranslation(?string $result, string $original): bool
    {
        return !empty($result) && trim($result) !== '' && strcasecmp(trim($result), trim($original)) !== 0;
    }

    /**
     * تقسیم متن به جملات
     * از PREG_SPLIT_DELIM_CAPTURE برای حفظ جداکننده‌ها استفاده نکردیم تا منطق گروه بندی ساده‌تر باشد.
     */
    private function splitIntoSentences(string $text): array
    {
        // تقسیم بر اساس نقطه، علامت تعجب، علامت سوال، یا علامت سوال فارسی، که با یک یا چند فضای خالی دنبال شود.
        // اضافه کردن جداکننده فارسی «؛» (نقطه ویرگول فارسی)
        $sentences = preg_split('/(?<=[.!?؟؛])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [$text];

        // اطمینان از حذف فضای خالی از ابتدا/انتهای هر جمله
        return array_map('trim', $sentences);
    }

    /**
     * گروه‌بندی جملات به chunks با اندازه مشخص
     * این منطق از splitIntoChunks قدیمی به ارث رسیده اما تنها بر اساس جملات کار می‌کند.
     */
    private function groupSentencesIntoChunks(array $sentences, int $maxSize): array
    {
        $chunks = [];
        $currentChunk = '';

        foreach ($sentences as $sentence) {
            $separator = empty($currentChunk) ? '' : ' '; // از فضای خالی به جای '. ' استفاده می‌کنیم

            $potential = $currentChunk . $separator . $sentence;

            // اگر جمله به تنهایی بزرگتر از maxSize باشد، آن را به کلمات تقسیم می‌کنیم (همان منطق قدیمی)
            if (mb_strlen($sentence) > $maxSize) {
                if (!empty($currentChunk)) {
                    $chunks[] = trim($currentChunk);
                    $currentChunk = '';
                }
                // تقسیم جمله طولانی به subchunks بر اساس کلمات
                array_push($chunks, ...$this->splitLongSentence($sentence, $maxSize));
                continue;
            }

            if (mb_strlen($potential) > $maxSize) {
                if (!empty($currentChunk)) {
                    $chunks[] = trim($currentChunk);
                }
                $currentChunk = $sentence;
            } else {
                $currentChunk = $potential;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }

        return $chunks;
    }

    /**
     * تقسیم جملات طولانی به subchunks بر اساس کلمات
     */
    private function splitLongSentence(string $sentence, int $maxSize): array
    {
        // تقسیم به کلمات با استفاده از فضای خالی
        $words = preg_split('/\s+/', $sentence, -1, PREG_SPLIT_NO_EMPTY);
        $chunks = [];
        $currentChunk = '';

        foreach ($words as $word) {
            $potential = $currentChunk ? $currentChunk . ' ' . $word : $word;

            if (mb_strlen($potential) > $maxSize) {
                // اگر حتی یک کلمه به تنهایی بزرگتر از maxSize باشد، آن را به عنوان یک chunk برگردان.
                if (empty($currentChunk) && mb_strlen($word) > $maxSize) {
                    $chunks[] = $word;
                    continue;
                }
                if (!empty($currentChunk)) {
                    $chunks[] = $currentChunk;
                }
                $currentChunk = $word;
            } else {
                $currentChunk = $potential;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }

        return $chunks;
    }

    /**
     * تولید کلید کش برای ترجمه
     */
    private function generateCacheKey(string $text, string $sourceLang, string $targetLang): string
    {
        return sprintf(
            '%s:google:%s-%s:%s',
            self::CACHE_PREFIX,
            $sourceLang,
            $targetLang,
            md5($text)
        );
    }

    /**
     * تولید کلید کش برای اصلاح املایی
     */
    private function generateSpellingCacheKey(string $text, string $sourceLang): string
    {
        return sprintf(
            '%s:%s:%s',
            self::SPELLING_CACHE_PREFIX,
            $sourceLang,
            md5($text)
        );
    }

    /**
     * یک آرایه خالی برای ترجمه‌ها برمی‌گرداند.
     */
    private function getEmptyTranslationsArray(): array
    {
        // از map استفاده می‌کنیم تا مطمئن شویم آرایه از نوع string, string است.
        return array_fill_keys(array_column(Locale::cases(), 'value'), '');
    }
}
