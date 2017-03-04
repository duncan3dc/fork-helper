<?php

namespace duncan3dc\ForkerTests;

use duncan3dc\Forker\SharedMemory;
use PHPUnit\Framework\TestCase;

class SharedMemoryTest extends TestCase
{
    private $memory;

    public function setUp()
    {
        $this->memory = new SharedMemory;
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

}
