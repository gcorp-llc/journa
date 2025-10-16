<?php

namespace App\Console\Commands;

use App\Jobs\CrawlCategoryLinksJob;
use Illuminate\Console\Command;

class NewsCrawlerCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'crawler:run {--site-id=}'; // اضافه کردن option برای فیلتر سایت خاص

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Run News Crawler';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        // دریافت siteId از ورودی
        $siteId = $this->option('site-id');

        // ارسال جاب به صف. (siteId می تواند null باشد)
        CrawlCategoryLinksJob::dispatch($siteId);

        $this->info('News crawler job dispatched successfully.');
    }
}
