<?php

namespace App\Services\DoctorCrawlers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class NobatExtractor implements ExtractorInterface
{
    private const BASE_URL = 'https://nobat.ir';
    private array $selectors;

    public function __construct(array $selectors)
    {
        $this->selectors = $selectors;
    }

    public function extract(Crawler $crawler, string $url): ?array
    {
        $name = $this->extractText($crawler, 'name');
        if (empty($name)) return null;

        $officeId = $this->extractAttribute($crawler, 'office_id', 'data-officeid');
        $description = $this->extractText($crawler, 'description');

        return [
            'profile_url'   => $url,
            'name'          => $name,
            'specialty'     => $this->extractText($crawler, 'specialty'),
            'code'          => $this->extractMedicalCode($crawler, 'code'),
            'address'       => $this->extractText($crawler, 'address'),
            'description'   => $description,
            'image_url'     => $this->makeAbsoluteUrl($this->extractAttribute($crawler, 'image', 'src')),
            'instagram'     => $this->makeAbsoluteUrl($this->extractAttribute($crawler, 'instagram', 'href')),
            'website'       => $this->extractWebsiteFromText($description),
            'office_id'     => $officeId,
            'phone_numbers' => $this->fetchPhoneNumbers($officeId),
            'experience'    => $this->extractText($crawler, 'experience'),
            'state'         => $this->extractText($crawler, 'state'),
        ];
    }

    private function fetchPhoneNumbers(?string $officeId): array
    {
        if (empty($officeId)) return [];
        try {
            $config = config('crawler.sites.nobat');
            $response = Http::timeout(15)->asForm()->post($config['phone_api'], [$config['phone_api_field'] => (int)$officeId]);
            if ($response->successful() && is_array($data = $response->json())) {
                return collect($data)->pluck('call')->filter()->map(fn($num) => $this->cleanupNumber($num))->unique()->values()->all();
            }
        } catch (\Exception $e) {
            Log::warning("[nobat] Failed to fetch phone numbers for office {$officeId}: " . $e->getMessage());
        }
        return [];
    }

    private function extractMedicalCode(Crawler $crawler, string $key): string
    {
        $selectors = (array) ($this->selectors[$key] ?? []);
        foreach ($selectors as $selector) {
            try {
                $container = $crawler->filter($selector);
                if ($container->count() > 0) {
                    foreach ($container->filter('span') as $spanNode) {
                        $spanText = trim((new Crawler($spanNode))->text());
                        if (Str::contains($spanText, 'شماره نظام')) {
                            return $spanText;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::debug("[nobat] Medical code selector '{$selector}' failed.", ['error' => $e->getMessage()]);
            }
        }
        return '';
    }

    private function extractText(Crawler $crawler, string $key): string
    {
        $selectors = (array) ($this->selectors[$key] ?? []);
        foreach ($selectors as $selector) {
            try {
                $node = $crawler->filter($selector);
                if ($node->count() > 0 && !empty($text = trim($node->text()))) {
                    return $text;
                }
            } catch (\Exception $e) {
                Log::debug("[nobat] Selector '{$selector}' failed.", ['error' => $e->getMessage()]);
            }
        }
        return '';
    }

    private function extractAttribute(Crawler $crawler, string $key, string $attribute): string
    {
        $selectors = (array) ($this->selectors[$key] ?? []);
        foreach ($selectors as $selector) {
            try {
                $node = $crawler->filter($selector);
                if ($node->count() > 0 && !empty($attr = trim($node->attr($attribute) ?? ''))) {
                    return $attr;
                }
            } catch (\Exception $e) {
                Log::debug("[nobat] Attribute selector '{$selector}' failed.", ['error' => $e->getMessage()]);
            }
        }
        return '';
    }

    private function makeAbsoluteUrl(?string $url): string
    {
        if (empty($url)) return '';
        if (Str::startsWith($url, ['http://', 'https://'])) return $url;
        return self::BASE_URL . Str::start($url, '/');
    }

    private function cleanupNumber(?string $number): string
    {
        if (empty($number)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $result = str_replace($persian, $english, $number);
        return str_replace($arabic, $english, $result);
    }
    
    private function extractWebsiteFromText(?string $text): string
    {
        if (empty($text)) return '';
        if (preg_match('/(?:https?:\/\/)?(?:www\.)?([a-z0-9\-\.]+\.[a-z]{2,})/i', $text, $matches)) {
            $site = $matches[0];
            if (!preg_match('~^(?:f|ht)tps?://~i', $site)) {
                $site = 'http://' . $site;
            }
            return $site;
        }
        return '';
    }
}