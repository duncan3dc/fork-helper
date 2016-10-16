<?php

namespace duncan3dc\Forker;

/**
 * Store exceptions in shared memory and retrieve them as string.
 */
final class SharedMemory
{
    /**
     * How much shared memory to allocate.
     */
    const LIMIT = 1000;

    /**
     * @var int $key The key to use.
     */
    private $key;


    /**
     * Create a new instance.
     */
    public function __construct()
    {
        $this->key = round(microtime(true) * 1000);

        $memory = shmop_open($this->key, "c", 0644, self::LIMIT);
        shmop_write($memory, serialize([]), 0);
        shmop_close($memory);
    }


    /**
     * Get the exception details out of shared memory.
     *
     * @param resource $memory The shmop resource from shmop_open()
     *
     * @return \Throwable[]
     */
    private function unserialize($memory): array
    {
        $data = shmop_read($memory, 0, self::LIMIT);

        $exceptions = unserialize($data);

        if (!is_array($exceptions)) {
            $exceptions = [];
        }

        return $exceptions;
    }


    /**
     * Add an exception the shared memory.
     *
     * @param \Throwable $exception The exception instance to add
     *
     * @return void
     */
    public function addException(\Throwable $exception)
    {
        $memory = shmop_open($this->key, "c", 0644, self::LIMIT);

        $exceptions = $this->unserialize($memory);

        $exceptions[] = get_class($exception) . ": " . $exception->getMessage() . " (" . $exception->getFile() . ":" . $exception->getLine() . ")";

        $data = serialize($exceptions);

        shmop_write($memory, $data, 0);
        shmop_close($memory);
    }


    /**
     * Get all the exceptions added to the shared memory.
     *
     * @return \Throwable[]
     */
    public function getExceptions(): array
    {
        $memory = shmop_open($this->key, "a", 0, 0);

        $exceptions = $this->unserialize($memory);

        shmop_delete($memory);
        shmop_close($memory);

        return $exceptions;
    }
}
