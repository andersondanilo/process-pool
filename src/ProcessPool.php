<?php

namespace ProcessPool;

use Iterator;
use ProcessPool\Events\ProcessEvent;
use ProcessPool\Events\ProcessFinished;
use ProcessPool\Events\ProcessStarted;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Process pool allow you to run a constant number
 * of parallel processes
 */
class ProcessPool
{
    /** @var Iterator<Process> */
    private Iterator $queue;

    /** @var array<Process> */
    private array $running = [];

    /** @var array<string, mixed> */
    private array $options;

    private int $concurrency;

    private EventDispatcherInterface $eventDispatcher;

    /**
     * Accept any type of iterator, inclusive Generator
     *
     * @param Iterator<Process> $queue
     * @param array<string, mixed> $options
     */
    public function __construct(Iterator $queue, array $options = [])
    {
        $this->eventDispatcher = new EventDispatcher();
        $this->queue = $queue;
        $this->options = array_merge($this->getDefaultOptions(), $options);
        $this->concurrency = $this->options['concurrency'];
    }

    /** @return array<string, mixed> */
    private function getDefaultOptions(): array
    {
        return [
            'concurrency' => '5',
            'eventPreffix' => 'process_pool',
            'throwExceptions' => false,
        ];
    }

    /**
     * Start and wait until all processes finishes
     */
    public function wait(): void
    {
        $this->startNextProcesses();

        while (count($this->running) > 0) {
            /** @var Process $process */
            foreach ($this->running as $key => $process) {
                $exception = null;
                try {
                    $process->checkTimeout();
                    $isRunning = $process->isRunning();
                } catch (RuntimeException $e) {
                    $isRunning = false;
                    $exception = $e;

                    if ($this->shouldThrowExceptions()) {
                        throw $e;
                    }
                }

                if (!$isRunning) {
                    unset($this->running[$key]);
                    $this->startNextProcesses();

                    $event = new ProcessFinished($process);

                    if ($exception) {
                        $event->setException($exception);
                    }

                    $this->dispatchEvent($event);
                }
            }
            usleep(1000);
        }
    }

    public function onProcessFinished(callable $callback): void
    {
        $eventName = $this->options['eventPreffix'] . '.' . ProcessEvent::PROCESS_FINISHED;
        $this->getEventDispatcher()->addListener($eventName, $callback);
    }

    /**
     * Start next processes until fill the concurrency limit
     */
    private function startNextProcesses(): void
    {
        $concurrency = $this->getConcurrency();

        while (count($this->running) < $concurrency && $this->queue->valid()) {
            $process = $this->queue->current();
            $process->start();

            $this->dispatchEvent(new ProcessStarted($process));

            $this->running[] = $process;

            $this->queue->next();
        }
    }

    private function shouldThrowExceptions(): bool
    {
        return (bool) $this->options['throwExceptions'];
    }

    /**
     * Get processes concurrency, default 5
     *
     * @return int
     */
    public function getConcurrency()
    {
        return $this->concurrency;
    }

    /**
     * @return static
     */
    public function setConcurrency(int $concurrency)
    {
        $this->concurrency = $concurrency;

        return $this;
    }

    private function dispatchEvent(ProcessEvent $event): void
    {
        $eventPreffix = $this->options['eventPreffix'];
        $eventName = $event->getName();

        $this->getEventDispatcher()->dispatch($event, "$eventPreffix.$eventName");
    }

    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * @return static
     */
    public function setEventDispatcher(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }
}
