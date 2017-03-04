<?php

namespace duncan3dc\ForkerTests\Fork;

use duncan3dc\Forker\AdapterInterface;
use duncan3dc\Forker\Exception;
use duncan3dc\Forker\Fork;
use duncan3dc\Forker\SingleThreadAdapter;
use Mockery;
use PHPUnit\Framework\TestCase;

class ForkTest extends TestCase
{
    private $fork;


    public function tearDown()
    {
        Mockery::close();
    }


    private function getMockAdapter()
    {
        $adapter = Mockery::mock(AdapterInterface::class);
        $adapter->shouldReceive("cleanup");

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
            public function wait(int $pid): int
            {
                return 0;
            }
            public function getExceptions(): array
            {
                return [];
            }
            public function cleanup(): void
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

        $pid = $this->fork->call("phpversion");
        $this->fork->wait();

        $this->assertSame(7, $pid);
    }


    public function testNoExceptionMock()
    {
        $adapter = $this->getMockAdapter();

        $adapter->shouldReceive("wait")->once()->with(5)->andReturn(0);

        $result = $this->fork->wait(5);
        $this->assertSame($this->fork, $result);
    }


    public function testNoExceptionSimple()
    {
        $this->getSimpleAdapter();

        $tmp = tempnam(sys_get_temp_dir(), "phpunit-fork-helper-");

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

        $this->expectException(Exception::class, "An error occurred within a thread, the return code was: 256");
        $adapter->shouldReceive("getExceptions")->once()->andReturn([]);
        $this->fork->wait();
    }


    public function testDestruct()
    {
        $fork = new Fork(new SingleThreadAdapter);

        $fork->call(function () {
            throw new \DomainException("¯\_(ツ)_/¯");
        });

        $this->expectException(Exception::class, "An error occurred within a thread, the return code was: 256");
        unset($fork);
    }
}
