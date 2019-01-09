<?php

namespace duncan3dc\Forker;

use duncan3dc\Forker\Interfaces\ForkInterface;

/**
 * Class to make multi-threaded processes easier.
 */
class Fork implements ForkInterface
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
                $adapter = new PcntlAdapter();
            } else {
                $adapter = new SingleThreadAdapter();
            }
        }

        $this->adapter = $adapter;
    }


    /**
     * @inheritdoc
     */
    public function call(callable $func, ...$args): int
    {
        $pid = $this->adapter->call($func, ...$args);

        $this->threads[$pid] = $pid;

        return $pid;
    }


    /**
     * @inheritdoc
     */
    public function isRunning(int $pid): bool
    {
        if (!array_key_exists($pid, $this->threads)) {
            return false;
        }

        $running = $this->adapter->isRunning($pid);

        if (!$running) {
            unset($this->threads[$pid]);
        }

        return $running;
    }


    /**
     * @inheritdoc
     */
    public function wait(int $pid = null): ForkInterface
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

        if ($error !== 0) {
            $exceptions = $this->adapter->getExceptions();

            $message = "An error occurred within a thread, the return code was: {$error}\n";
            foreach ($exceptions as $exception) {
                $message .= "  - {$exception}\n";
            }

            $this->adapter->cleanup();
            throw new Exception($message, $error);
        }

        $this->adapter->cleanup();
        return $this;
    }


    /**
     * @inheritdoc
     */
    public function getPIDs(): array
    {
        return array_values($this->threads);
    }
}
