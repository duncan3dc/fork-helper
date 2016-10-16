<?php

namespace duncan3dc\Forker;

/**
 * Class to make multi-threaded processes easier.
 */
class Fork
{
    /**
     * @var AdapterInterface $adapter The adapter to handle the code execution.
     */
    private $adapter;

    /**
     * @var array $threads The threads created.
     */
    private $threads = [];


    /**
     * Create a container to run multiple threads.
     *
     * @param AdapterInterface $adapter The adapter to use to handle the threading
     */
    public function __construct(AdapterInterface $adapter = null)
    {
        if ($adapter === null) {
            if (function_exists("pcntl_fork")) {
                $adapter = new PcntlAdapter;
            } else {
                $adapter = new SingleThreadAdapter;
            }
        }

        $this->adapter = $adapter;
    }


    /**
     * Run some code in a thread.
     *
     * @param callable $func The function to execute
     * @param mixed $args The arguments to pass to the function
     *
     * @return int The pid of the thread created to execute this code
     */
    public function call(callable $func, ...$args): int
    {
        $pid = $this->adapter->call($func, ...$args);

        $this->threads[$pid] = $pid;

        return $pid;
    }


    /**
     * Wait for the processes started via call().
     *
     * @param int $pid The pid to wait for, if none is passed then all threads created by this object will be waited for
     *
     * @return void
     */
    public function wait($pid = null)
    {
        if ($pid) {
            $threads = [$pid];
        } else {
            $threads = $this->threads;
        }

        $error = 0;
        $status = 0;
        foreach ($threads as $pid) {
            $status = $this->adapter->wait($pid);
            if ($status > 0) {
                $error = $status;
            }
            unset($this->threads[$pid]);
        }

        # If no errors occured then we're done
        if ($error === 0) {
            return;
        }

        $exceptions = $this->adapter->getExceptions();

        $message = "An error occurred within a thread, the return code was: {$error}\n";
        foreach ($exceptions as $exception) {
            $message .= "  - {$exception}\n";
        }
        throw new Exception($message, $error);
    }


    /**
     * Get forks' PIDs.
     *
     * @return int[]
     */
    public function getPIDs(): array
    {
        return array_values($this->threads);
    }


    /**
     * If no call to wait() is made, then we wait for the threads on destruct.
     */
    public function __destruct()
    {
        $this->wait();
    }
}
