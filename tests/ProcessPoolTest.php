<?php

namespace Tests;

use ProcessPool\Events\ProcessFinished;
use ProcessPool\ProcessPool;
use Symfony\Component\Process\Process;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Generator;

class ProcessPoolTest extends TestCase
{
    public function testWorksWithGenerators()
    {
        $this->assertFinishProcessesIn(6, 5);
    }

    public function testHandleExceptions()
    {
        $this->assertFinishProcessesIn(2, 5, 1);
    }

    private function assertFinishProcessesIn($expectedTime, $countProcesses, $timeout = null)
    {
        $processes = $this->makeSleepProcesses($countProcesses, $timeout);
        $countFinished = 0;


        $pool = new ProcessPool($processes);
        $pool->setConcurrency(2);
        $pool->onProcessFinished(function ($event) use (&$countFinished) {
            $process = $event->getProcess();
            $exception = $event->getException();
            $countFinished++;
        });

        // test set event handler
        $pool->setEventDispatcher($pool->getEventDispatcher());

        $start = microtime(true);

        $pool->wait();

        $this->assertEquals($expectedTime, round(microtime(true) - $start), 'assert duration');
        $this->assertEquals($countProcesses, $countFinished);
    }

    public function testThrowExceptions()
    {
        $processes = $this->makeSleepProcesses(6, 5);
        $pool = new ProcessPool($processes, ['throwExceptions' => true]);
        $pool->setConcurrency(6);

        $this->expectException(ProcessTimedOutException::class);

        $pool->wait();
    }

    private function makeSleepProcesses($count, $timeout = null): Generator
    {
        for ($i = 0; $i < $count; $i++) {
            $process = new Process(['sleep', $i]);

            if (!is_null($timeout)) {
                $process->setTimeout($timeout);
            }

            yield $process;
        }
    }
}
