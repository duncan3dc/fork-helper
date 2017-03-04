<?php

namespace duncan3dc\ForkerTests;

use duncan3dc\Forker\SharedMemory;
use duncan3dc\ObjectIntruder\Intruder;
use PHPUnit\Framework\TestCase;

class SharedMemoryTest extends TestCase
{
    private $memory;

    public function setUp()
    {
        error_reporting(\E_ALL);

        $this->memory = new SharedMemory;
    }


    public function testConstructor()
    {
        # Avoid warning/notices
        error_reporting(0);

        # Ensure that two instances are not given the same key
        $memory1 = new SharedMemory;
        $memory2 = new SharedMemory;

        $key1 = (new Intruder($memory1))->key;
        $key2 = (new Intruder($memory2))->key;

        $this->assertGreaterThan($key1, $key2);
    }


    public function testNoExceptions()
    {
        $this->assertSame([], $this->memory->getExceptions());
    }


    public function testOneException()
    {
        $this->memory->addException(new \RuntimeException("Whoops"));

        $exceptions = $this->memory->getExceptions();

        $this->assertSame(1, count($exceptions));

        $this->assertStringMatchesFormat("RuntimeException: Whoops (%s:%i)", $exceptions[0]);
    }


    public function testMultipleExceptions()
    {
        $this->memory->addException(new \RuntimeException("Whoops"));
        $this->memory->addException(new \DomainException("Nope"));

        $exceptions = $this->memory->getExceptions();

        $this->assertSame(2, count($exceptions));

        $this->assertStringMatchesFormat("RuntimeException: Whoops (%s:%i)", $exceptions[0]);
        $this->assertStringMatchesFormat("DomainException: Nope (%s:%s)", $exceptions[1]);
    }


    public function testRetrievedExceptions()
    {
        $this->memory->addException(new \RuntimeException("Whoops"));
        $exceptions = $this->memory->getExceptions();
        $this->assertSame(1, count($exceptions));
        $this->assertStringMatchesFormat("RuntimeException: Whoops (%s:%i)", $exceptions[0]);

        $exceptions = $this->memory->getExceptions();
        $this->assertSame(0, count($exceptions));

        $this->memory->addException(new \DomainException("Nope"));
        $exceptions = $this->memory->getExceptions();
        $this->assertSame(1, count($exceptions));
        $this->assertStringMatchesFormat("DomainException: Nope (%s:%s)", $exceptions[0]);
    }
}
