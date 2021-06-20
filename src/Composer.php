<?php

namespace BotMan\Studio;

use Illuminate\Foundation\Application;
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
        $process = $this->installationCommandProcess($package);

        $process->run($callback);

        return $process->isSuccessful();
    }

    /**
     * Get installation command for process.
     *
     * @param  string $package
     * @return \Symfony\Component\Process\Process
     */
    protected function installationCommandProcess($package)
    {
        if (version_compare(Application::VERSION, '5.8.0', '<')) {
            $process = $this->getProcess();

            return $process->setCommandLine(trim($this->findComposer().' require '.$package));
        }

        $command = $this->findComposer();
        array_push($command, 'require', $package);

        return $this->getProcess($command);
    }
}
