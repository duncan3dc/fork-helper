<?php

namespace duncan3dc\Forker\Interfaces;

interface ForkInterface
{
    /**
     * Run some code in a thread.
     *
     * @param callable $func The function to execute
     * @param mixed ...$args The arguments to pass to the function
     *
     * @return int The pid of the thread created to execute this code
     */
    public function call(callable $func, ...$args): int;

    /**
     * Check if a thread is running.
     *
     * @param int $pid
     *
     * @return bool
     */
    public function isRunning(int $pid): bool;

    /**
     * Wait for the processes started via call().
     *
     * @param int $pid The pid to wait for, if none is passed then all threads created by this object will be waited for
     *
     * @return $this
     */
    public function wait(int $pid = null): self;

    /**
     * Get the pids of any running processes.
     *
     * @return int[]
     */
    public function getPIDs(): array;
}
