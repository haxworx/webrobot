<?php

namespace App\Service;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Docker
{
    public $containerId = "";

    public function __construct(string $containerId)
    {
        $this->containerId = $containerId;
    }

    public function stop(): bool
    {
        $success = true;

        $process = new Process(['sudo', '-u', 'spider', 'docker', 'container', 'stop', $this->containerId]);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            $success = false;
        }

        return $success;
    }
}


?>
