<?php

namespace duncan3dc\Helpers;

class Fork
{
    const SHARED_MEMORY_LIMIT = 1000;
    private $threads;
    private $memoryKey;
    public  $ignoreErrors;


    public function __construct()
    {
        $this->threads = [];
        $this->ignoreErrors = false;
        $this->memoryKey = round(microtime(true) * 1000);
    }


    public function call($func, $args = null)
    {
        $pid = pcntl_fork();

        if ($pid == -1) {
            throw new \Exception("Failed to fork");
        }

        # If this is the child process, then run the requested function
        if (!$pid) {
            try {
                if ($args === null) {
                    $func();
                } else {
                    call_user_func($func, $args);
                }
            } catch(\Exception $e) {
                $memory = shmop_open($this->memoryKey, "c", 0644, static::SHARED_MEMORY_LIMIT);
                $errors = shmop_read($memory, 0, static::SHARED_MEMORY_LIMIT);
                $errors = trim($errors);
                if ($errors) {
                    $errors .= "\n";
                }
                $errors .= "Exception: " . $e->getMessage() . " (" . $e->getFile() . ":" . $e->getLine() . ")";
                shmop_write($memory, $errors, 0);
                shmop_close($memory);
                exit(1);
            }

            # Then we must exit or else we will end up the child process running the parent processes code
            die();
        }

        $this->threads[$pid] = $pid;

        return $pid;
    }


    public function wait($pid = false)
    {
        if ($pid) {
            $threads = [$pid];
        } else {
            $threads = $this->threads;
        }

        $error = false;
        foreach ($threads as $pid) {
            pcntl_waitpid($pid, $status);
            if ($status > 0) {
                $error = $status;
            }
            unset($this->threads[$pid]);
        }

        if (!$this->ignoreErrors && $error) {
            $memory = shmop_open($this->memoryKey, "a", 0, 0);
            $errors = shmop_read($memory, 0, static::SHARED_MEMORY_LIMIT);
            shmop_delete($memory);
            shmop_close($memory);

            $error = "An error occurred within a thread, the return code was (" . $error . ")";
            if ($errors = trim($errors)) {
                $error .= "\n" . $errors;
            }
            throw new \Exception($error);
        }

        return $status;
    }


    public function __destruct()
    {
        $this->wait();
    }
}
