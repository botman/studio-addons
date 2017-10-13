<?php

namespace Tests;

use Spatie\Menu\Laravel\Menu;
use Orchestra\Testbench\TestCase;
use BotMan\Studio\Providers\RouteServiceProvider;
use BotMan\Studio\Providers\StudioServiceProvider;
use BotMan\Studio\Providers\DriverServiceProvider;

class ProviderTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.url', 'http://localhosts');
    }

    protected function getPackageProviders($app)
    {
        return [
            StudioServiceProvider::class,
            DriverServiceProvider::class,
            RouteServiceProvider::class
        ];
    }

    /** @test */
    public function it_registers_menu()
    {
        $menu = $this->app->make('studio.menu');
        $this->assertInstanceOf(Menu::class, $menu);
    }
}