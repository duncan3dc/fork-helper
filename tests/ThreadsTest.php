<?php

namespace duncan3dc\ForkerTests;

use duncan3dc\Forker\Interfaces\ForkInterface;
use duncan3dc\Forker\Threads;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class ThreadsTest extends TestCase
{
    /** @var Threads */
    private $threads;

    /** @var ForkInterface|MockInterface */
    private $fork;


    public function setUp()
    {
        $this->fork = Mockery::mock(ForkInterface::class);
        $this->threads = new Threads(3, $this->fork);
    }


    public function testCall1()
    {
        $this->fork->shouldReceive("getPIDs")->once()->with()->andReturn([]);
        $this->fork->shouldReceive("call")->once()->with("sleep", 1)->andReturn(77);
        $result = $this->threads->call("sleep", 1);
        $this->assertSame(77, $result);
    }


    public function testCall2()
    {
        # Simulate the maximum number of threads being in use
        $this->fork->shouldReceive("getPIDs")->once()->with()->andReturn([1, 2, 3]);

        # Before calling the code an exited thread must be found
        $this->fork->shouldReceive("isRunning")->once()->with(1)->andReturn(false);

        $this->fork->shouldReceive("call")->once()->with("sleep", 1)->andReturn(66);
        $result = $this->threads->call("sleep", 1);
        $this->assertSame(66, $result);
    }


    public function testCall3()
    {
        # Simulate the maximum number of threads being in use
        $this->fork->shouldReceive("getPIDs")->once()->with()->andReturn([1, 2, 3]);

        # Before calling the code an exited thread must be found
        $this->fork->shouldReceive("isRunning")->once()->with(1)->andReturn(true);
        $this->fork->shouldReceive("isRunning")->once()->with(2)->andReturn(true);
        $this->fork->shouldReceive("isRunning")->once()->with(3)->andReturn(true);
        $this->fork->shouldReceive("isRunning")->once()->with(1)->andReturn(false);

        $this->fork->shouldReceive("call")->once()->with("sleep", 1)->andReturn(55);
        $result = $this->threads->call("sleep", 1);
        $this->assertSame(55, $result);
    }


    public function testIsRunning1()
    {
        $this->fork->shouldReceive("isRunning")->once()->with(456)->andReturn(true);
        $result = $this->threads->isRunning(456);
        $this->assertSame(true, $result);
    }


    public function testIsRunning2()
    {
        $this->fork->shouldReceive("isRunning")->once()->with(456)->andReturn(false);
        $result = $this->threads->isRunning(456);
        $this->assertSame(false, $result);
    }


    public function testWait1()
    {
        $this->fork->shouldReceive("wait")->once()->with(123)->andReturn($this->fork);
        $result = $this->threads->wait(123);
        $this->assertSame($this->threads, $result);
    }


    public function testWait2()
    {
        $this->fork->shouldReceive("wait")->once()->with(null)->andReturn($this->fork);
        $result = $this->threads->wait();
        $this->assertSame($this->threads, $result);
    }


    public function testGetPIDs()
    {
        $this->fork->shouldReceive("getPids")->once()->with()->andReturn([1, 2, 3]);
        $result = $this->threads->getPIDs();
        $this->assertSame([1, 2, 3], $result);
    }
}
