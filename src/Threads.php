<?php

namespace duncan3dc\Forker;

use duncan3dc\Forker\Interfaces\ForkInterface;
use function count;

/**
 * A collection of threads to execute code on.
 */
final class Threads implements ForkInterface
{
    /** @var ForkInterface */
    private $fork;

    /** @var int The maximum number of threads to use */
    private $limit;


    /**
     * Create a container to run multiple threads.
     *
     * @param int $limit The maximum number of threads to use
     * @param ForkInterface $fork
     */
    public function __construct(int $limit = 10, ForkInterface $fork = null)
    {
        $this->limit = $limit;

        if ($fork === null) {
            $fork = new Fork();
        }
        $this->fork = $fork;
    }


    /**
     * @inheritdoc
     */
    public function call(callable $func, ...$args): int
    {
        if (\count($this->getPIDs()) >= $this->limit) {
            $this->waitForAnyThread();
        }

        $pid = $this->fork->call($func, ...$args);

        return $pid;
    }


    /**
     * @inheritdoc
     */
    public function isRunning(int $pid): bool
    {
        return $this->fork->isRunning($pid);
    }


    /**
     * @inheritdoc
     */
    public function wait(int $pid = null): ForkInterface
    {
        $this->fork->wait($pid);
        return $this;
    }


    /**
     * @inheritdoc
     */
    public function getPIDs(): array
    {
        return $this->fork->getPIDs();
    }


    /**
     * If we've exhausted our limit of threads, wait for one to finish.
     *
     * @return void
     */
    private function waitForAnyThread()
    {
        while (true) {
            foreach ($this->getPIDs() as $pid) {
                if (!$this->isRunning($pid)) {
                    return;
                }
            }

            # If none of the threads have finished let, wait for a second before checking again
            \sleep(1);
        }
    }
}
