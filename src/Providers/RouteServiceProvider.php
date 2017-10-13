<?php

namespace BotMan\Studio\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'BotMan\Studio';

    /**
     * Pass BotMan Studio menu to all studio views.
     */
    public function register()
    {
        $this->mapStudioRoutes();
    }

    /**
     * Add additional BotMan Studio routes.
     */
    protected function addRoutes()
    {
    }

    /**
     * Define the "studio" routes for the application.
     * These routes are used for the BotMan studio
     * backend.
     *
     * @return void
     */
    protected function mapStudioRoutes()
    {
        Route::middleware('web')
             ->prefix('studio')
             ->namespace($this->namespace)
             ->group(function () {
                 $this->addRoutes();
             });
    }
}
