<?php

namespace ProcessPool\Events;

use Symfony\Component\Process\Process;

class ProcessStarted extends ProcessEvent
{
    const NAME = 'process_started';
}
