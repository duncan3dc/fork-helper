<?php

namespace duncan3dc\Helpers;

class Fork {

    private $threads;
    public  $ignoreErrors;


    public function __construct() {

        $this->threads = [];
        $this->ignoreErrors = false;

    }


    public function call($func) {

        $pid = pcntl_fork();

        if($pid == -1) {
            throw new \Exception("Failed to fork");
            return false;
        }

        # If this is the child process, then run the requested function
        if(!$pid) {
            $func();

            # Then we must exit or else we will end up the child process running the parent processes code
            die();
        }

        $this->threads[$pid] = $pid;

        return $pid;

    }


    public function wait($pid=false) {

        if($pid) {
            $threads = array($pid);
        } else {
            $threads = $this->threads;
        }

        $error = false;
        foreach($threads as $pid) {
            pcntl_waitpid($pid,$status);
            if($status > 0) {
                $error = $status;
            }
            unset($this->threads[$pid]);
        }

        if(!$this->ignoreErrors && $error) {
            throw new \Exception("An error occurred within a thread, the return code was (" . $error . ")");
        }

        return $status;

    }


    public function __destruct() {

        $this->wait();

    }


}
