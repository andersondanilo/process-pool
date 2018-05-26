<?php

namespace ProcessPool\Events;

use Exception;
use Symfony\Component\Process\Process;

class ProcessFinished extends ProcessEvent
{
    const NAME = 'process_finished';

    private $exception;

    /**
     * @param Exception $exception
     *
     * @return static
     */
    public function setException(Exception $exception)
    {
        $this->exception = $exception;

        return $this;
    }

    /**
     * @return Exception
     */
    public function getException()
    {
        return $this->exception;
    }
}
