<?php

namespace duncan3dc\ForkerTests;

use duncan3dc\Forker\AdapterInterface;
use duncan3dc\Forker\Exception;
use duncan3dc\Forker\Fork;
use duncan3dc\Forker\Interfaces\ForkInterface;
use duncan3dc\Forker\PcntlAdapter;
use Mockery;
use PHPUnit\Framework\TestCase;

use function is_string;

class ForkTest extends TestCase
{
    /** @var ForkInterface */
    private $fork;


    public function tearDown()
    {
        Mockery::close();
    }


    private function getMockAdapter()
    {
        $adapter = Mockery::mock(AdapterInterface::class);
        $this->fork = new Fork($adapter);

        return $adapter;
    }


    private function getSimpleAdapter()
    {
        $adapter = new class implements AdapterInterface {
            public function call(callable $func, ...$args): int
            {
                $func(...$args);
                return rand(1, 999);
            }
            public function isRunning(int $pid): bool
            {
                return false;
            }
            public function wait(int $pid): int
            {
                return 0;
            }
            public function getExceptions(): array
            {
                return [];
            }
            public function cleanup()
            {
            }
        };

        $this->fork = new Fork($adapter);
    }


    public function testCallReturnsPID()
    {
        $adapter = $this->getMockAdapter();

        $adapter->shouldReceive("call")->once()->andReturn(7);
        $adapter->shouldReceive("wait")->once()->with(7)->andReturn(0);
        $adapter->shouldReceive("cleanup")->once()->with();

        $pid = $this->fork->call("phpversion");
        $this->fork->wait();

        $this->assertSame(7, $pid);
    }


    public function testNoExceptionMock()
    {
        $adapter = $this->getMockAdapter();

        $adapter->shouldReceive("wait")->once()->with(5)->andReturn(0);
        $adapter->shouldReceive("cleanup")->once()->with();

        $result = $this->fork->wait(5);
        $this->assertSame($this->fork, $result);
    }


    public function testNoExceptionSimple()
    {
        $this->getSimpleAdapter();

        $tmp = tempnam(sys_get_temp_dir(), "phpunit-fork-helper-");
        $this->assertTrue(is_string($tmp));

        $this->fork->call(function ($tmp) {
            file_put_contents($tmp, "success!");
        }, $tmp);

        $this->assertSame("success!", file_get_contents($tmp));
    }


    public function testCallArguments()
    {
        $this->getSimpleAdapter();

        $this->expectOutputString("value1\nvalue2");

        $this->fork->call(function ($line1, $line2) {
            echo "{$line1}\n{$line2}";
        }, "value1", "value2");
    }


    public function testIsRunning1()
    {
        $adapter = $this->getMockAdapter();

        $adapter->shouldReceive("call")->once()->with("sleep")->andReturn(123);
        $this->fork->call("sleep");

        $adapter->shouldReceive("isRunning")->once()->with(123)->andReturn(true);
        $this->assertTrue($this->fork->isRunning(123));

        $this->assertSame([123], $this->fork->getPIDs());
    }


    public function testIsRunning2()
    {
        $adapter = $this->getMockAdapter();

        $adapter->shouldReceive("call")->once()->with("sleep")->andReturn(123);
        $this->fork->call("sleep");

        $adapter->shouldReceive("isRunning")->once()->with(123)->andReturn(false);
        $this->assertFalse($this->fork->isRunning(123));

        $this->assertSame([], $this->fork->getPIDs());
    }


    public function testIsRunning3()
    {
        $this->getMockAdapter();

        $this->assertFalse($this->fork->isRunning(123));
    }


    public function testGetPIDs()
    {
        $this->getSimpleAdapter();

        $pids[] = $this->fork->call("phpversion");
        $pids[] = $this->fork->call("phpversion");

        $this->assertSame($pids, $this->fork->getPids());
    }


    public function testGetPIDsAfterWait()
    {
        $this->getSimpleAdapter();

        $pids[] = $this->fork->call("phpversion");
        $pids[] = $this->fork->call("phpversion");

        $pid = array_shift($pids);

        $result = $this->fork->wait($pid);
        $this->assertSame($this->fork, $result);
    }


    public function testWaitWithErrors()
    {
        $adapter = $this->getMockAdapter();

        $adapter->shouldReceive("call")->once()->andReturn(2);
        $adapter->shouldReceive("wait")->once()->with(2)->andReturn(0);
        $this->fork->call("phpversion");

        $adapter->shouldReceive("call")->once()->andReturn(5);
        $adapter->shouldReceive("wait")->once()->with(5)->andReturn(256);
        $this->fork->call("phpversion");

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("An error occurred within a thread, the return code was: 256");
        $adapter->shouldReceive("getExceptions")->once()->andReturn([]);

        $adapter->shouldReceive("cleanup")->once()->with();

        $this->fork->wait();
    }

    public function testMultipleWaits()
    {
        $duration = 10 ** 5;

        $sleep = function () use ($duration) {
            usleep($duration);
        };


        $sut = new Fork(new PcntlAdapter());
        $time = microtime(true);

        $pid = $sut->call($sleep);
        $sut->wait($pid);

        $pid = $sut->call($sleep);
        $sut->wait($pid);
        $actualDuration = microtime(true) - $time;
        $expectedDuration = 2 * $duration / 10 ** 6;
        $this->assertTrue(
            $actualDuration > $expectedDuration,
            "Process should wait for $expectedDuration sec, but only $actualDuration sec was waited"
        );
    }

    public function testSingleWaitForMultipleProcesses()
    {
        $duration = 10 ** 5;

        $sleep = function () use ($duration) {
            usleep($duration);
        };


        $sut = new Fork(new PcntlAdapter());
        $time = microtime(true);

        foreach (range(0, 5) as $item) {
            $sut->call($sleep);
        }
        $sut->wait();

        $actualDuration = microtime(true) - $time;

        $expectedDuration = $duration / 10 ** 6;
        $this->assertTrue(
            $actualDuration > $expectedDuration,
            "Process should wait for $expectedDuration sec, but only $actualDuration sec was waited"
        );

        $this->assertTrue(
            $actualDuration < 2 * $expectedDuration,
            "Process should wait for near $expectedDuration sec, but $actualDuration sec was waited"
        );
    }

    public function testProcessesThrowsExceptions()
    {
        $throw = function ($text, $code) {
            throw new \RuntimeException($text, $code);
        };
        $sut = new Fork(new PcntlAdapter());

        foreach (range(0, 5) as $item) {
            $sut->call($throw, "Exception $item", $item);
        }

        try {
            $sut->wait();
            $this->fail("Exception should be thrown on errors in child processes");
        } catch (Exception $e) {
            $this->assertCount(8, explode("\n", $e->getMessage()));
        }
    }
}
