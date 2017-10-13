<?php

namespace BotMan\Studio\Providers;

use View;
use Illuminate\Http\Request;
use Spatie\Menu\Laravel\Menu;
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

        $this->registerRouteHelper();

        $this->registerMenu();
    }

    /**
     * Pass BotMan Studio menu to all studio views.
     */
    public function boot()
    {
        View::composer('studio::*', function ($view) {
            $view->with('menu', $this->app->make('studio.menu'));
        });
    }

    /**
     * Auto-discover BotMan commands and load them.
     */
    public function discoverCommands()
    {
        $this->commands(Discovery::getInstance()->get('botman/commands'));
    }

    /**
     * Register BotMan Studio related route helpers.
     */
    protected function registerRouteHelper()
    {
        Request::macro('section', function () {
            if ($this->segment(1) === 'studio') {
                return 'studio';
            }
            return 'web';
        });
        Request::macro('isWeb', function () {
            return request()->section() === 'web';
        });
        Request::macro('isStudio', function () {
            return request()->section() === 'studio';
        });
    }

    /**
     * Register BotMan Studio Menus.
     */
    protected function registerMenu()
    {
        $this->app->singleton('studio.menu', function ($app) {
            return Menu::new()
                ->setActiveFromRequest();
        });
    }
}
