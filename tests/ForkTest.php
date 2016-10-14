<?php

namespace duncan3dc\ForkerTests\Fork;

use duncan3dc\Forker\Exception;
use duncan3dc\Forker\Fork;

class ForkTest extends \PHPUnit_Framework_TestCase
{

    public function testOutput()
    {
        $output = "";
        $memoryKey = round(microtime(true) * 1000);
        $memoryLimit = 100;

        $writeToMemory = function ($string) use (&$output, $memoryKey, $memoryLimit) {
            $memory = shmop_open($memoryKey, "c", 0644, $memoryLimit);
            $output = shmop_read($memory, 0, $memoryLimit);
            if ($output = trim($output)) {
                $output .= "\n";
            }
            $output .= $string;
            shmop_write($memory, $output, 0);
            shmop_close($memory);
        };

        $fork = new Fork;

        $fork->call(function () use ($writeToMemory) {
            $writeToMemory("func1.1");
            usleep(80000);
            $writeToMemory("func1.2");
        });

        $fork->call(function () use ($writeToMemory) {
            usleep(50000);
            $writeToMemory("func2.1");
            usleep(50000);
            $writeToMemory("func2.2");
        });

        usleep(75000);
        $writeToMemory("waiting");

        $fork->wait();
        $writeToMemory("end");

        $memory = shmop_open($memoryKey, "a", 0, 0);
        $output = trim(shmop_read($memory, 0, $memoryLimit));
        shmop_delete($memory);
        shmop_close($memory);

        $this->assertSame("func1.1\nfunc2.1\nwaiting\nfunc1.2\nfunc2.2\nend", $output);
    }


    public function testException()
    {
        $fork = new Fork;

        $this->setExpectedException(Exception::class, "An error occurred within a thread, the return code was: 256");
        $fork->call(function () {
            throw new \InvalidArgumentException("Test");
        });

        $fork->wait();
    }


    public function testGetPIDs()
    {
        $fork = new Fork;

        $pids[] = $fork->call("phpversion");
        $pids[] = $fork->call("phpversion");

        $this->assertSame($pids, $fork->getPids());
    }


    public function testGetPIDsAfterWait()
    {
        $fork = new Fork;

        $pids[] = $fork->call("phpversion");
        $pids[] = $fork->call("phpversion");

        $pid = array_shift($pids);
        $fork->wait($pid);

        $this->assertSame($pids, $fork->getPids());
    }


    public function testNoException()
    {
        $fork = new Fork;

        $tmp = tempnam(sys_get_temp_dir(), "phpunit-fork-helper-");

        $pid = $fork->call(function ($tmp) {
            file_put_contents($tmp, "success!");
        }, $tmp);

        $fork->wait($pid);

        $this->assertSame("success!", file_get_contents($tmp));
    }
}
