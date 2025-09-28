<?php

namespace App\Console\Commands;

use App\Jobs\CrawlCategoryLinksJob;
use Illuminate\Console\Command;

class NewsCrawlerCommand extends Command
{
    protected $signature = 'crawler:run {--site-id=}'; // اضافه کردن option برای فیلتر سایت خاص

    protected $description = 'Run News Crawler';

    public function handle()
    {
        $siteId = $this->option('site-id');
        CrawlCategoryLinksJob::dispatch($siteId); // پاس دادن siteId اگر وجود داشته باشد
        $this->info('News crawler finished.');
    }
}
