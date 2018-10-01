<?php

namespace BotMan\Studio\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class BotManCacheClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'botman:cache:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all cached conversations.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     * @return mixed
     */
    public function handle(Filesystem $files)
    {
        $cacheFolder = storage_path('botman');

        if ($files->exists($cacheFolder)) {
            $files->cleanDirectory($cacheFolder);
        }

        $this->info('BotMan cache cleared!');
    }
}
