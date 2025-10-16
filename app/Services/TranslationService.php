<?php

namespace App\Services;

use App\Enums\Locale;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Throwable;

class TranslationService
{
    // ثوابت (Constants) جایگزین مقادیر کانفیگ شدند
    private const CACHE_PREFIX = 'translation';
    private const SPELLING_CACHE_PREFIX = 'spelling_correction';
    private const CHUNK_SIZE = 4000;
    private const GOOGLE_SENTENCE_CHUNK_SIZE = 2000;
    private const CACHE_TTL_HOURS = 168; // 1 هفته کشینگ
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY_SECONDS = 2;

    /**
     * ترجمه متن به همه زبان‌های تعریف شده در `Locale` enum.
     *
     * @param string $text متن ورودی برای ترجمه
     * @param string|null $sourceLang زبان مبدا (در صورت خالی بودن، به صورت خودکار تشخیص داده می‌شود)
     * @return array<string, string> آرایه‌ای از ترجمه‌ها با کلید زبان
     */
    public function translateToAll(string $text, ?string $sourceLang = null): array
    {
        $text = trim($text);

        if (empty($text)) {
            return $this->getEmptyTranslationsArray();
        }

        // تشخیص زبان مبدأ
        $sourceLang = $sourceLang ?? $this->detectLanguage($text);

        // اصلاح املایی (Spelling Correction)
        $text = $this->correctSpelling($text, $sourceLang) ?? $text;

        $translations = [$sourceLang => $text];

        foreach (Locale::cases() as $targetLocale) {
            $targetLang = $targetLocale->value;

            if ($targetLang === $sourceLang) {
                continue;
            }

            $translated = $this->translate($text, $targetLang, $sourceLang);

            // استفاده از fallback فقط در صورت واقعی شکست خوردن ترجمه
            if ($this->isTranslationFailed($translated, $text)) {
                Log::warning('Translation failed, attempting fallback', [
                    'source' => $sourceLang,
                    'target' => $targetLang
                ]);
                $translated = $this->translateWithFallback($text, $targetLang, $sourceLang);
            }

            $translations[$targetLang] = $translated;
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
            $translatedData[$field] = isset($data[$field]) && !empty(trim($data[$field]))
                ? $this->translateToAll($data[$field])
                : $this->getEmptyTranslationsArray();
        }

        return $translatedData;
    }

    /**
     * زبان متن را با استفاده از الگوهای کاراکتری تشخیص می‌دهد.
     */
    public function detectLanguage(string $text): string
    {
        $sample = mb_substr($text, 0, 400);

        // بررسی فارسی - کاراکترهای منحصر به فرد فارسی
        if (preg_match('/[پچژگکی]/u', $sample)) {
            return Locale::FA->value;
        }

        // بررسی عربی - کاراکترهای منحصر به فرد عربی
        if (preg_match('/[ضصثقطظذ]/u', $sample)) {
            return Locale::AR->value;
        }

        // بررسی انگلیسی
        if (preg_match('/[a-zA-Z]/u', $sample)) {
            return Locale::EN->value;
        }

        return Locale::EN->value;
    }

    /**
     * ترجمه یک متن با استفاده از Google Translate.
     * این متد متن‌های طولانی را به قطعات کوچک‌تر تقسیم می‌کند.
     */
    private function translate(string $text, string $targetLang, string $sourceLang): string
    {
        $chunkSize = self::CHUNK_SIZE;

        if (mb_strlen($text) <= $chunkSize) {
            return $this->translateChunk($text, $targetLang, $sourceLang);
        }

        return $this->translateLongText($text, $targetLang, $sourceLang, $chunkSize);
    }

    /**
     * ترجمه متن‌های طولانی با تقسیم به chunks
     *
     * نکته: تأخیر (usleep) بین chunkها برای بهینه‌سازی سرعت حذف شد.
     */
    private function translateLongText(string $text, string $targetLang, string $sourceLang, int $chunkSize): string
    {
        $chunks = $this->splitIntoChunks($text, $chunkSize);
        $translatedChunks = [];

        Log::info('Translating chunked text', [
            'chunks' => count($chunks),
            'source' => $sourceLang,
            'target' => $targetLang
        ]);

        foreach ($chunks as $chunk) {
            $translatedChunks[] = $this->translateChunk($chunk, $targetLang, $sourceLang);
        }

        return implode("\n\n", $translatedChunks);
    }

    /**
     * یک قطعه از متن را با استفاده از کش و Google Translate ترجمه می‌کند.
     */
    private function translateChunk(string $text, string $targetLang, string $sourceLang): string
    {
        $cacheKey = $this->generateCacheKey($text, $sourceLang, $targetLang);
        $cacheTtl = self::CACHE_TTL_HOURS;

        return Cache::remember(
            $cacheKey,
            now()->addHours($cacheTtl),
            fn() => $this->translateWithGoogle($text, $targetLang, $sourceLang)
        );
    }

    /**
     * ترجمه با استفاده از کتابخانه Google Translate.
     */
    private function translateWithGoogle(string $text, string $targetLang, string $sourceLang): string
    {
        $sentenceChunkSize = self::GOOGLE_SENTENCE_CHUNK_SIZE;

        if (mb_strlen($text) <= $sentenceChunkSize) {
            return $this->translateGoogleSingleChunk($text, $targetLang, $sourceLang);
        }

        return $this->translateGoogleWithSentenceSplitting($text, $targetLang, $sourceLang, $sentenceChunkSize);
    }

    /**
     * ترجمه با تقسیم به جملات
     */
    private function translateGoogleWithSentenceSplitting(
        string $text,
        string $targetLang,
        string $sourceLang,
        int $chunkSize
    ): string {
        $sentences = $this->splitIntoSentences($text);
        $chunks = $this->groupSentencesIntoChunks($sentences, $chunkSize);
        $translatedChunks = [];

        foreach ($chunks as $chunk) {
            $translated = $this->translateGoogleSingleChunk($chunk, $targetLang, $sourceLang);
            // در صورت شکست ترجمه، از متن اصلی استفاده کن
            $translatedChunks[] = !empty($translated) ? $translated : $chunk;
        }

        return implode('. ', $translatedChunks);
    }

    /**
     * ترجمه یک chunk کوچک با Google با retry.
     */
    private function translateGoogleSingleChunk(
        string $chunk,
        string $targetLang,
        string $sourceLang,
        ?int $maxRetries = null
    ): string {
        $maxRetries = $maxRetries ?? self::MAX_RETRIES;
        $retryDelay = self::RETRY_DELAY_SECONDS;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                // تایم‌آوت 30 ثانیه برای اطمینان از کامل شدن درخواست
                $translator = new GoogleTranslate($targetLang, $sourceLang, ['timeout' => 30]);
                $result = $translator->translate($chunk);

                if ($this->isValidTranslation($result, $chunk)) {
                    return trim($result);
                }
            } catch (Throwable $e) {
                Log::warning("Google Translate failed", [
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                    'error' => $e->getMessage(),
                    'source' => $sourceLang,
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
     */
    private function correctSpelling(string $text, string $sourceLang): ?string
    {
        if ($sourceLang === Locale::EN->value) {
            return null;
        }

        $cacheKey = $this->generateSpellingCacheKey($text, $sourceLang);
        $cacheTtl = self::CACHE_TTL_HOURS;

        return Cache::remember($cacheKey, now()->addHours($cacheTtl), function () use ($text, $sourceLang) {
            // 1. ترجمه به انگلیسی
            $toEnglish = $this->translateWithGoogle($text, Locale::EN->value, $sourceLang);

            if (empty($toEnglish)) {
                return null;
            }

            // 2. ترجمه بازگشتی به زبان مبدأ
            $backToSource = $this->translateWithGoogle($toEnglish, $sourceLang, Locale::EN->value);

            return !empty($backToSource) ? $backToSource : null;
        });
    }

    /**
     * ترجمه با استفاده از مسیر fallback (از طریق انگلیسی)
     */
    private function translateWithFallback(string $text, string $targetLang, string $sourceLang): string
    {
        // 1. ترجمه به انگلیسی
        $intermediate = $this->translate($text, Locale::EN->value, $sourceLang);

        if (!empty($intermediate) && $intermediate !== $text) {
            // 2. ترجمه از انگلیسی به زبان مقصد
            $final = $this->translate($intermediate, $targetLang, Locale::EN->value);
            return !empty($final) ? $final : $text;
        }

        return $text;
    }

    /**
     * بررسی اینکه آیا ترجمه شکست خورده است (ترجمه خالی است یا عین متن اصلی)
     */
    private function isTranslationFailed(?string $translated, string $original): bool
    {
        return empty($translated) || trim($translated) === '' || $translated === $original;
    }

    /**
     * بررسی معتبر بودن ترجمه
     */
    private function isValidTranslation(?string $result, string $original): bool
    {
        return !empty($result) && trim($result) !== '' && trim($result) !== trim($original);
    }

    /**
     * تقسیم متن به جملات
     */
    private function splitIntoSentences(string $text): array
    {
        // تقسیم بر اساس نقطه، علامت تعجب، علامت سوال، یا علامت سوال فارسی
        return preg_split('/(?<=[.!?؟])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [$text];
    }

    /**
     * گروه‌بندی جملات به chunks با اندازه مشخص
     */
    private function groupSentencesIntoChunks(array $sentences, int $maxSize): array
    {
        $chunks = [];
        $currentChunk = '';

        foreach ($sentences as $sentence) {
            $potential = $currentChunk ? $currentChunk . '. ' . $sentence : $sentence;

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
     * متن را به قطعات کوچک‌تر بر اساس جملات تقسیم می‌کند.
     */
    private function splitIntoChunks(string $text, int $chunkSize): array
    {
        $sentences = $this->splitIntoSentences($text);
        $chunks = [];
        $currentChunk = '';

        foreach ($sentences as $sentence) {
            // اگر جمله خیلی طولانی است، آن را به کلمات تقسیم کن
            if (mb_strlen($sentence) > $chunkSize) {
                if (!empty($currentChunk)) {
                    $chunks[] = trim($currentChunk);
                    $currentChunk = '';
                }

                // تقسیم جمله طولانی به subchunks بر اساس کلمات
                array_push($chunks, ...$this->splitLongSentence($sentence, $chunkSize));
                continue;
            }

            $potential = $currentChunk ? $currentChunk . ' ' . $sentence : $sentence;

            if (mb_strlen($potential) > $chunkSize) {
                $chunks[] = trim($currentChunk);
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
        return array_fill_keys(array_column(Locale::cases(), 'value'), '');
    }
}
