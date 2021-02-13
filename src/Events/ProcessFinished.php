<?php

namespace ProcessPool\Events;

use Exception;
use Symfony\Component\Process\Process;

class ProcessFinished extends ProcessEvent
{
    private ?Exception $exception = null;

    public function getName(): string
    {
        return static::PROCESS_FINISHED;
    }

    public function setException(Exception $exception): self
    {
        $this->exception = $exception;

        return $this;
    }

    public function getException(): ?Exception
    {
        return $this->exception;
    }
}
