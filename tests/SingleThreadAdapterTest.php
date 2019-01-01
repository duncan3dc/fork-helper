<?php

namespace duncan3dc\ForkerTests;

use duncan3dc\Forker\Exception;
use duncan3dc\Forker\Fork;
use duncan3dc\Forker\SingleThreadAdapter;
use PHPUnit\Framework\TestCase;

class SingleThreadAdapterTest extends TestCase
{
    private $adapter;
    private $fork;

    public function setUp()
    {
        $this->adapter = new SingleThreadAdapter();
        $this->fork = new Fork($this->adapter);
    }


    public function testOutput()
    {
        $this->expectOutputString("func1.1\nfunc1.2\nfunc2.1\nfunc2.2\nwaiting\nend");

        $this->fork->call(function () {
            echo "func1.1\n";
            echo "func1.2\n";
        });

        $this->fork->call(function () {
            echo "func2.1\n";
            echo "func2.2\n";
        });

        echo "waiting\n";
        $this->fork->wait();

        echo "end";
    }


    public function testException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("An error occurred within a thread, the return code was: 256\n  - InvalidArgumentException: Test");

        $this->fork->call(function () {
            throw new \InvalidArgumentException("Test");
        });

        $this->fork->wait();
    }


    public function testWait()
    {
        $pid = $this->adapter->call("phpversion");

        $status = $this->adapter->wait($pid);

        $this->assertSame(0, $status);
    }


    public function testCatchException()
    {
        $pid = $this->adapter->call(function () {
            throw new \RuntimeException("Test");
        });

        $status = $this->adapter->wait($pid);
        $this->assertSame(256, $status);

        $exceptions = $this->adapter->getExceptions();
        $this->assertSame(1, count($exceptions));
        $this->assertInstanceOf(\RuntimeException::class, $exceptions[0]);
        $this->assertSame("Test", $exceptions[0]->getMessage());
    }


    public function testCatchExceptionsAndHandleSuccess()
    {
        $pid1 = $this->adapter->call(function () {
            throw new \InvalidArgumentException("Nope");
        });

        $pid2 = $this->adapter->call("phpversion");

        $pid3 = $this->adapter->call(function () {
            throw new \DomainException("Fail");
        });

        $status = $this->adapter->wait($pid1);
        $this->assertSame(256, $status);

        $status = $this->adapter->wait($pid2);
        $this->assertSame(0, $status);

        $status = $this->adapter->wait($pid3);
        $this->assertSame(256, $status);

        $exceptions = $this->adapter->getExceptions();
        $this->assertSame(2, count($exceptions));
        $this->assertInstanceOf(\InvalidArgumentException::class, $exceptions[0]);
        $this->assertSame("Nope", $exceptions[0]->getMessage());
        $this->assertInstanceOf(\DomainException::class, $exceptions[1]);
        $this->assertSame("Fail", $exceptions[1]->getMessage());
    }
}
