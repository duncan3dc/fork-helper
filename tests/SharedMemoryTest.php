<?php

namespace duncan3dc\ForkerTests;

use duncan3dc\Forker\SharedMemory;
use duncan3dc\ObjectIntruder\Intruder;
use PHPUnit\Framework\Error\Error;
use PHPUnit\Framework\TestCase;

class SharedMemoryTest extends TestCase
{
    /** @var Intruder */
    private $memory;

    protected function setUp(): void
    {
        error_reporting(\E_ALL);

        $memory = new SharedMemory();
        $this->memory = new Intruder($memory);
    }


    protected function tearDown(): void
    {
        try {
            $this->memory->delete();
        } catch (\Throwable $e) {
        }
    }


    public function testConstructor(): void
    {
        # Avoid warning/notices
        error_reporting(0);

        # Ensure that two instances are not given the same key
        $memory1 = new SharedMemory();
        $memory2 = new SharedMemory();

        $key1 = (new Intruder($memory1))->key;
        $key2 = (new Intruder($memory2))->key;

        $this->assertGreaterThan($key1, $key2);

        $memory1->delete();
        $memory2->delete();
    }


    public function testNoExceptions(): void
    {
        $this->assertSame([], $this->memory->getExceptions());
    }


    public function testOneException(): void
    {
        $this->memory->addException(new \RuntimeException("Whoops"));

        $exceptions = $this->memory->getExceptions();

        $this->assertSame(1, count($exceptions));

        $this->assertStringMatchesFormat("RuntimeException: Whoops (%s:%i)", $exceptions[0]);
    }


    public function testMultipleExceptions(): void
    {
        $this->memory->addException(new \RuntimeException("Whoops"));
        $this->memory->addException(new \DomainException("Nope"));

        $exceptions = $this->memory->getExceptions();

        $this->assertSame(2, count($exceptions));

        $this->assertStringMatchesFormat("RuntimeException: Whoops (%s:%i)", $exceptions[0]);
        $this->assertStringMatchesFormat("DomainException: Nope (%s:%s)", $exceptions[1]);
    }


    public function testRetrievedExceptions(): void
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


    public function testDelete(): void
    {
        # Get the shared memory key that the instance is using
        $key = $this->memory->key;

        $this->memory->delete();

        # Make sure the memory has been cleaned up by attempting to access it
        $this->expectException(Error::class);
        if (version_compare(PHP_VERSION, '8.0.0', '<')) {
            $this->expectExceptionMessage("shmop_open(): unable to attach or create shared memory segment 'No such file or directory'");
        } else {
            $this->expectExceptionMessage('shmop_open(): Unable to attach or create shared memory segment "No such file or directory"');
        }
        shmop_open($key, "a", 0, 0);
    }
}
