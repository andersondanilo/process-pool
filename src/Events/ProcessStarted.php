<?php

namespace ProcessPool\Events;

use Symfony\Component\Process\Process;

class ProcessStarted extends ProcessEvent
{
    public function getName(): string
    {
        return static::PROCESS_STARTED;
    }
}
