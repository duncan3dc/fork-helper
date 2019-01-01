<?php

namespace duncan3dc\ForkerTests;

use duncan3dc\Forker\Exception;
use duncan3dc\Forker\Fork;
use duncan3dc\Forker\PcntlAdapter;
use PHPUnit\Framework\TestCase;
use function assert;
use function is_resource;

class PcntlAdapterTest extends TestCase
{
    private $fork;

    public function setUp()
    {
        $this->fork = new Fork(new PcntlAdapter);
    }


    public function testOutput()
    {
        $output = "";
        $memoryKey = ftok(__FILE__, "t");
        $memoryLimit = 100;

        $writeToMemory = function ($string) use (&$output, $memoryKey, $memoryLimit) {
            $memory = shmop_open($memoryKey, "c", 0644, $memoryLimit);
            assert(is_resource($memory));
            $output = shmop_read($memory, 0, $memoryLimit);
            if ($output = trim($output)) {
                $output .= "\n";
            }
            $output .= $string;
            shmop_write($memory, $output, 0);
            shmop_close($memory);
        };

        $this->fork->call(function () use ($writeToMemory) {
            $writeToMemory("func1.1");
            usleep(80000);
            $writeToMemory("func1.2");
        });

        $this->fork->call(function () use ($writeToMemory) {
            usleep(50000);
            $writeToMemory("func2.1");
            usleep(50000);
            $writeToMemory("func2.2");
        });

        usleep(75000);
        $writeToMemory("waiting");

        $this->fork->wait();
        $writeToMemory("end");

        $memory = shmop_open($memoryKey, "a", 0, 0);
        assert(is_resource($memory));
        $output = trim(shmop_read($memory, 0, $memoryLimit));
        shmop_delete($memory);
        shmop_close($memory);

        $this->assertSame("func1.1\nfunc2.1\nwaiting\nfunc1.2\nfunc2.2\nend", $output);
    }


    public function testException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("An error occurred within a thread, the return code was: 256\n  - InvalidArgumentException: Extra");

        $this->fork->call(function () {
            throw new \InvalidArgumentException("Extra");
        });

        $this->fork->wait();
    }


    public function testNoException()
    {
        $this->fork->call("phpversion");

        $result = $this->fork->wait();
        $this->assertSame($this->fork, $result);
    }
}
