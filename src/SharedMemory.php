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
        # Avoid creating 2 instances using the same memory segment
        usleep(1000);

        $this->key = (int) (microtime(true) * 1000);

        # Initialise the memory
        $memory = $this->getMemory();
        shmop_write($memory, serialize([]), 0);
        shmop_close($memory);
    }


    /**
     * Get the shared memory segment.
     *
     * @return resource
     */
    private function getMemory()
    {
        $memory = shmop_open($this->key, "c", 0644, self::LIMIT);

        if (!$memory) {
            throw new Exception("Unable to open the shared memory block");
        }

        return $memory;
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
        $memory = $this->getMemory();

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
        $memory = $this->getMemory();

        $exceptions = $this->unserialize($memory);

        shmop_write($memory, serialize([]), 0);

        shmop_close($memory);

        return $exceptions;
    }


    /**
     * Delete the shared memory this instance represents.
     *
     * @return void
     */
    public function delete()
    {
        $memory = shmop_open($this->key, "a", 0, 0);
        if ($memory) {
            shmop_delete($memory);
            shmop_close($memory);
        }
    }
}
