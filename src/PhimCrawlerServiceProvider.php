<?php

namespace Phim\Crawler\PhimCrawler;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as SP;
use Phim\Crawler\PhimCrawler\Console\CrawlerScheduleCommand;
use Phim\Crawler\PhimCrawler\Option;

class PhimCrawlerServiceProvider extends SP
{
    /**
     * Get the policies defined on the provider.
     *
     * @return array
     */
    public function policies()
    {
        return [];
    }

    public function register()
    {

        config(['plugins' => array_merge(config('plugins', []), [
            'hacoidev/3nguon-crawler' =>
            [
                'name' => 'Apii.Online Crawler',
                'package_name' => 'hacoidev/3nguon-crawler',
                'icon' => 'la la-code-fork',
                'entries' => [
                    ['name' => 'Crawler', 'icon' => 'la la-hand-grab-o', 'url' => backpack_url('/plugin/3nguon-crawler')],
                    ['name' => 'Option', 'icon' => 'la la-cog', 'url' => backpack_url('/plugin/3nguon-crawler/options')],
                ],
            ]
        ])]);

        config(['logging.channels' => array_merge(config('logging.channels', []), [
            '3nguon-crawler' => [
                'driver' => 'daily',
                'path' => storage_path('logs/hacoidev/3nguon-crawler.log'),
                'level' => env('LOG_LEVEL', 'debug'),
                'days' => 7,
            ],
        ])]);

        config(['3nguon.updaters' => array_merge(config('3nguon.updaters', []), [
            [
                'name' => '3nguon Crawler',
                'handler' => 'Phim\Crawler\PhimCrawler\Crawler'
            ]
        ])]);
    }

    public function boot()
    {
        $this->commands([
            CrawlerScheduleCommand::class,
        ]);

        $this->app->booted(function () {
            $this->loadScheduler();
        });

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', '3nguon-crawler');
    }

    protected function loadScheduler()
    {
        $schedule = $this->app->make(Schedule::class);
        $schedule->command('3nguon:plugins:3nguon-crawler:schedule')->cron(Option::get('crawler_schedule_cron_config', '*/10 * * * *'))->withoutOverlapping();
    }
}
