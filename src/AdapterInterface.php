<?php

namespace duncan3dc\Forker;

interface AdapterInterface
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
     * Wait for the a thread started via call() to end.
     *
     * @param int $pid The pid to wait for
     *
     * @return int The exit code of the thread
     */
    public function wait(int $pid): int;


    /**
     * Get any exceptions thrown by the threads.
     *
     * @return \Throwable[]
     */
    public function getExceptions(): array;


    /**
     * Method to be called when the adapter is finished with.
     *
     * A destructor can't be used as instances in
     * forked threads will be destroyed twice.
     *
     * @return void
     */
    public function cleanup();
}
