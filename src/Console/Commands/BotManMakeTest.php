<?php

namespace BotMan\Studio\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class BotManMakeTest extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'botman:make:test {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new test class.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Test';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/test.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\..\tests\Botman';
    }
}
