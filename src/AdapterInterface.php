<?php

namespace duncan3dc\Forker;

interface AdapterInterface
{

    /**
     * Run some code in a thread.
     *
     * @param callable $func The function to execute
     * @param mixed $args The arguments to pass to the function
     *
     * @return int The pid of the thread created to execute this code
     */
    public function call(callable $func, ...$args): int;


    /**
     * Wait for the a thread started via call() to end.
     *
     * @param int $pid The pid to wait for
     *
     * @return int The exit code of the thread
     */
    public function wait($pid): int;


    /**
     * Get any exceptions thrown by the threads.
     *
     * @return \Throwable[]
     */
    public function getExceptions(): array;
}
