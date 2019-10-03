<?php

namespace BotMan\Studio;

use Illuminate\Support\Composer as BaseComposer;

class Composer extends BaseComposer
{
    /**
     * Install a composer package.
     *
     * @param $package
     * @param callable $callback
     */
    public function install($package, callable $callback)
    {
        $command = array_merge($this->findComposer(), ['require', $package]);

        $process = $this->getProcess($command);

        $process->run($callback);

        return $process->isSuccessful();
    }
}
