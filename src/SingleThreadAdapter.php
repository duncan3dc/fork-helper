<?php

namespace duncan3dc\Forker;

/**
 * Handle executing code in a single thread.
 */
final class SingleThreadAdapter implements AdapterInterface
{
    /**
     * @var int $pid The latest process ID issued.
     */
    private $pid = 0;

    /**
     * @var \Throwable[] $exceptions Caught exceptions from the threads.
     */
    private $exceptions = [];

    /**
     * @var int[] $status The exit code for each thread.
     */
    private $status = [];


    /**
     * Run some code in a thread.
     *
     * @param callable $func The function to execute
     * @param mixed ...$args The arguments to pass to the function
     *
     * @return int The pid of the thread created to execute this code
     */
    public function call(callable $func, ...$args): int
    {
        $pid = ++$this->pid;

        $this->status[$pid] = 0;

        try {
            $func(...$args);
        } catch (\Throwable $e) {
            $this->exceptions[] = $e;
            $this->status[$pid] = 256;
        }

        return $pid;
    }


    /**
     * @inheritdoc
     */
    public function isRunning(int $pid): bool
    {
        return false;
    }


    /**
     * Wait for the a thread started via call() to end.
     *
     * @param int $pid The pid to wait for
     *
     * @return int The exit code of the thread
     */
    public function wait(int $pid): int
    {
        return $this->status[$pid];
    }


    /**
     * Get any exceptions thrown by the threads.
     *
     * @return \Throwable[]
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }


    /**
     * Method to be called when the adapter is finished with.
     *
     * @return void
     */
    public function cleanup()
    {
        $this->pid = 0;
        $this->exceptions = [];
        $this->status = [];
    }
}
