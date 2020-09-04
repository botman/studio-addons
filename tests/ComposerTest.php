<?php

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ComposerTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    /** @test */
    public function it_install_package()
    {
        $composer = $this->getMockedComposer();

        $composer->install('foo/bar', function () {
            $this->assertTrue(true);
        });
    }

    protected function getMockedComposer()
    {
        $composer = m::mock('BotMan\Studio\Composer[getProcess]', [new Filesystem(), __DIR__])
            ->shouldAllowMockingProtectedMethods();
        $process = m::mock('Symfony\Component\Process\Process');

        if (version_compare(Application::VERSION, '5.8.0', '<')) {
            $process->shouldReceive('setCommandLine')->with('composer require foo/bar')->andReturnSelf();
            $composer->shouldReceive('getProcess')->once()->andReturn($process);
        } else {
            $composer->shouldReceive('getProcess')->once()->with(['composer', 'require', 'foo/bar'])->andReturn($process);
        }

        $process->shouldReceive('run')->once()->with(m::type('Closure'))->andReturnUsing(function ($callable) {
            $callable();

            return 0;
        })->shouldReceive('isSuccessful')->once()->andReturnTrue();

        return $composer;
    }
}
