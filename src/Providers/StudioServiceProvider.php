<?php

namespace BotMan\Studio\Providers;

use BotMan\Studio\Console\Commands\BotManCacheClear;
use BotMan\Studio\Console\Commands\BotManInstallDriver;
use BotMan\Studio\Console\Commands\BotManListDrivers;
use BotMan\Studio\Console\Commands\BotManMakeConversation;
use BotMan\Studio\Console\Commands\BotManMakeMiddleware;
use BotMan\Studio\Console\Commands\BotManMakeTest;
use Illuminate\Support\ServiceProvider;
use TheCodingMachine\Discovery\Discovery;

class StudioServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->commands([
            BotManListDrivers::class,
            BotManInstallDriver::class,
            BotManMakeMiddleware::class,
            BotManMakeConversation::class,
            BotManMakeTest::class,
            BotManCacheClear::class,
        ]);

        $this->discoverCommands();
    }

    /**
     * Auto-discover BotMan commands and load them.
     */
    public function discoverCommands()
    {
        $this->commands(Discovery::getInstance()->get('botman/commands'));
    }
}
