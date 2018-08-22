<?php

namespace duncan3dc\Forker;

/**
 * Handle multi-threading using the pcntl module.
 */
final class PcntlAdapter implements AdapterInterface
{
    /**
     * @var SharedMemory $memory Caught exceptions from the threads.
     */
    private $memory;


    /**
     * Run some code in a thread.
     *
     * @param callable $func The function to execute
     * @param mixed ...$args The arguments to pass to the function
     *
     * @return int The pid of the thread created to execute this code
     * @throws Exception
     */
    public function call(callable $func, ...$args): int
    {
        $memory = $this->allocateMemory();
        $pid = pcntl_fork();

        if ($pid == -1) {
            throw new Exception("Failed to fork");
        }

        # If the child process was started then return its pid
        if ($pid) {
            return $pid;
        }

        # If this is the child process, then run the requested function
        try {
            $func(...$args);
        } catch (\Throwable $e) {
            $memory->addException($e);
            exit(1);
        }

        # Then we must exit or else we will end up the child process running the parent processes code
        die();
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
        $status = 0;

        pcntl_waitpid($pid, $status);

        return $status;
    }


    /**
     * Get any exceptions thrown by the threads.
     *
     * @return \Throwable[]
     */
    public function getExceptions(): array
    {
        return $this->memory->getExceptions();
    }


    /**
     * Method to be called when the adapter is finished with.
     *
     * @return void
     * @throws \LogicException
     */
    public function cleanup()
    {
        if (!$this->memory) {
            throw new \LogicException("Cleanup should be used after allocate.");
        }

        $this->memory->delete();
        $this->memory = null;
    }

    private function allocateMemory(): SharedMemory
    {
        if (!$this->memory) {
            $this->memory = new SharedMemory();
        }

        return $this->memory;
    }
}
