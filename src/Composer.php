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
        $composer = $this->findComposer();

        $command = array_merge(
            (array) $composer,
            ['require'],
            array_filter(array_map('trim', explode(' ', $package)))
        );

        if (is_array($composer)) {
            $process = $this->getProcess($command);
        } else {
            $process = $this->getProcess();
            $process->setCommandLine(trim(implode(' ', $command)));
        }

        $process->run($callback);

        return $process->isSuccessful();
    }
}
