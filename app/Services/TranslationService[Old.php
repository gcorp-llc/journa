<?php

namespace App\Services;

use App\Enums\Locale;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslationService
{
    private const CHUNK_SIZE = 2900;
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY_SECONDS = 2;
    private const CACHE_TTL_HOURS = 168; // 1 week

    /**
     * یک متن را به تمام زبان‌های پشتیبانی شده ترجمه می‌کند.
     * @param string $text متنی که باید ترجمه شود.
     * @param string|null $sourceLang زبان مبدأ (اختیاری).
     */
    public function translateToAll(string $text, ?string $sourceLang = null): array
    {
        if (empty(trim($text))) {
            return $this->getEmptyTranslationsArray();
        }

        $sourceLang = $sourceLang ?? $this->detectLanguage($text);

        $translations = [];
        foreach (Locale::cases() as $targetLocale) {
            $targetLang = $targetLocale->value;
            $translations[$targetLang] = ($targetLang === $sourceLang)
                ? $text
                : $this->translate($text, $targetLang, $sourceLang);
        }

        return $translations;
    }

    /**
     * **متد بازگردانده شده:**
     * فیلدهای مشخص شده از یک آرایه را به صورت گروهی ترجمه می‌کند.
     */
    public function translateArray(array $data, array $fields): array
    {
        $translatedData = [];
        foreach ($fields as $field) {
            $translatedData[$field] = !empty($data[$field])
                ? $this->translateToAll($data[$field])
                : $this->getEmptyTranslationsArray();
        }
        return $translatedData;
    }

    public function detectLanguage(string $text): string
    {
        $sample = mb_substr($text, 0, 500);

        if (preg_match('/[پچژگ]/u', $sample)) {
            return Locale::FA->value;
        }

        $scores = [
            Locale::FA->value => preg_match_all('/[\x{0600}-\x{06FF}]/u', $sample),
            Locale::EN->value => preg_match_all('/[a-zA-Z]/u', $sample),
        ];

        arsort($scores);
        $detectedLang = key($scores);

        return ($scores[$detectedLang] > $scores[Locale::EN->value]) ? $detectedLang : Locale::EN->value;
    }

    private function translate(string $text, string $targetLang, string $sourceLang): ?string
    {
        if (mb_strlen($text) <= self::CHUNK_SIZE) {
            return $this->translateWithRetry($text, $targetLang, $sourceLang);
        }

        $chunks = $this->splitIntoChunks($text);
        $translatedChunks = [];

        foreach ($chunks as $chunk) {
            $translatedChunk = $this->translateWithRetry($chunk, $targetLang, $sourceLang);
            if ($translatedChunk === null) {
                Log::error("Failed to translate a chunk.", compact('targetLang', 'sourceLang'));
                return null;
            }
            $translatedChunks[] = $translatedChunk;
            usleep(500000); // 0.5-second delay
        }

        return implode("\n\n", $translatedChunks);
    }

    private function translateWithRetry(string $text, string $targetLang, string $sourceLang): ?string
    {
        $cacheKey = 'translation:' . md5($text . $sourceLang . $targetLang);

        return Cache::remember($cacheKey, now()->addHours(self::CACHE_TTL_HOURS), function () use ($text, $targetLang, $sourceLang) {
            for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {
                try {
                    $translator = new GoogleTranslate($targetLang, $sourceLang, ['timeout' => 40]);
                    $result = $translator->translate($text);
                    if (!empty($result)) return $result;
                } catch (\Throwable $e) {
                    Log::warning("Translation attempt #{$attempt} failed", ['error' => $e->getMessage()]);
                    if ($attempt < self::MAX_RETRIES) sleep(self::RETRY_DELAY_SECONDS);
                }
            }
            return null;
        });
    }

    private function splitIntoChunks(string $text): array
    {
        $chunks = [];
        $currentChunk = '';
        $sentences = preg_split('/(?<=[.!?؟])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($sentences as $sentence) {
            if (mb_strlen($sentence) > self::CHUNK_SIZE) {
                if (!empty($currentChunk)) {
                    $chunks[] = $currentChunk;
                    $currentChunk = '';
                }
                foreach (mb_str_split($sentence, self::CHUNK_SIZE) as $subChunk) {
                    $chunks[] = $subChunk;
                }
                continue;
            }

            if (mb_strlen($currentChunk) + mb_strlen($sentence) + 1 > self::CHUNK_SIZE) {
                $chunks[] = $currentChunk;
                $currentChunk = $sentence;
            } else {
                $currentChunk .= (empty($currentChunk) ? '' : ' ') . $sentence;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }

        return $chunks;
    }

    private function getEmptyTranslationsArray(): array
    {
        return array_fill_keys(array_column(Locale::cases(), 'value'), '');
    }
}