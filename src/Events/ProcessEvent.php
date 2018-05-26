<?php

namespace ProcessPool\Events;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Process\Process;

abstract class ProcessEvent extends Event
{
    /**
     * @var Process
     */
    private $process;

    /**
     * @param Process $process
     */
    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        return $this->process;
    }
}
