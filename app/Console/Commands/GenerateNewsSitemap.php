<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use App\Models\News;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url as SitemapUrl;

class GenerateNewsSitemap extends Command
{
    protected $signature = 'sitemap:news {--locale= : Specific locale (fa, en, ar)}';
    protected $description = 'Generate sitemap for News model with multilingual support';

    protected array $locales = ['fa', 'en', 'ar'];
    protected const CHUNK_SIZE = 500;
    protected const PUBLIC_SITEMAP_DIR = 'sitemaps/news';

    public function handle()
    {
        $localeOption = $this->option('locale');
        $localesToProcess = $localeOption ? [$localeOption] : $this->locales;

        $globalIndex = [];

        foreach ($localesToProcess as $locale) {
            $this->info("Processing locale: {$locale}");

            $localeDir = public_path(self::PUBLIC_SITEMAP_DIR . '/' . $locale);
            if (!File::exists($localeDir)) {
                File::makeDirectory($localeDir, 0755, true);
            }

            $sitemapIndex = Sitemap::create();

            // پردازش اخبار
            $this->processNews($locale, $sitemapIndex);

            // ذخیره index هر زبان
            $indexPath = "{$localeDir}/sitemap_index.xml";
            $sitemapIndex->writeToFile($indexPath);

            $this->info("✅ Sitemap for locale '{$locale}' created: {$indexPath}");

            $globalIndex[] = [
                'loc' => URL::to(self::PUBLIC_SITEMAP_DIR . "/{$locale}/sitemap_index.xml"),
                'lastmod' => Carbon::now()->toAtomString(),
            ];
        }

        $this->buildGlobalIndex($globalIndex);
        $this->info("🌍 Global news sitemap index created successfully.");
    }

    protected function processNews(string $locale, Sitemap $sitemapIndex)
    {
        $query = News::query()->select(['id', 'slug', 'updated_at'])->orderBy('id');
        $total = $query->count();

        if ($total === 0) {
            $this->warn("No news found.");
            return;
        }

        $this->info("Processing {$total} news records...");

        $query->chunkById(self::CHUNK_SIZE, function ($items, $chunkIndex) use ($locale, $sitemapIndex) {
            $chunkSitemap = Sitemap::create();

            foreach ($items as $item) {
                $url = "https://journa.ir/{$locale}/news/{$item->slug}";

                $chunkSitemap->add(
                    SitemapUrl::create($url)
                        ->setLastModificationDate($item->updated_at ?? Carbon::now())
                        ->setChangeFrequency(SitemapUrl::CHANGE_FREQUENCY_DAILY)
                        ->setPriority(0.8)
                );
            }

            $localeDir = public_path(self::PUBLIC_SITEMAP_DIR . '/' . $locale);
            $chunkFile = "{$localeDir}/news-chunk-{$chunkIndex}.xml";
            $chunkSitemap->writeToFile($chunkFile);

            $sitemapIndex->add(
                SitemapUrl::create(URL::to(self::PUBLIC_SITEMAP_DIR . "/{$locale}/" . basename($chunkFile)))
                    ->setLastModificationDate(Carbon::now())
            );

            $this->info("Chunk {$chunkIndex} created: {$chunkFile}");
        });
    }

    protected function buildGlobalIndex(array $sitemaps)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><sitemapindex/>');
        $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($sitemaps as $sitemap) {
            $sm = $xml->addChild('sitemap');
            $sm->addChild('loc', htmlspecialchars($sitemap['loc']));
            $sm->addChild('lastmod', $sitemap['lastmod']);
        }

        $path = public_path('sitemap.xml');
        $xml->asXML($path);
    }
}
