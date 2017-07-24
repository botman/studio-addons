<?php

namespace BotMan\Studio\Providers;

use Illuminate\Support\ServiceProvider;
use TheCodingMachine\Discovery\Discovery;
use BotMan\Studio\Console\Commands\BotManListDrivers;
use BotMan\Studio\Console\Commands\BotManInstallDriver;

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
