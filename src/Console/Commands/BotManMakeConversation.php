<?php

namespace BotMan\Studio\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class BotManMakeConversation extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'botman:make:conversation {name} {--example}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new conversation class.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Conversation';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('example')) {
            return __DIR__.'/stubs/conversation.example.stub';
        }

        return __DIR__.'/stubs/conversation.plain.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Conversations';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['example', 'e', InputOption::VALUE_OPTIONAL, 'Generate a conversation with an example.'],
        ];
    }
}
