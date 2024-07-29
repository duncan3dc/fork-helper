Changelog
=========

## x.y.z - UNRELEASED

--------

## 2.5.0 - 2024-07-29

### Changed

* [Support] Added support for PHP 8.1, 8.2, and 8.3.

--------

## 2.4.0 - 2021-08-15

### Changed

* [Support] Added support for PHP 7.4 and 8.0
* [Support] Dropped support for PHP 7.1, 7.2, and 7.3.

--------

## 2.3.0 - 2019-01-10

### Added

* [Threads] A new class to allow a maximum number of threads to be used.
* [Forking] An `isRunning()` method to check if a child process is running or not.
* [ForkInterface] Created an interface for the main `Fork` class.

### Changed

* [Support] Added support for PHP 7.2 and 7.3.
* [Support] Dropped support for PHP 7.0.

--------

## 2.2.1 - 2018-08-22

### Fixed

* [SharedMemory] Fix shared memory allocation [#10](https://github.com/duncan3dc/fork-helper/pull/10).

--------

## 2.2.0 - 2017-07-11

### Changed

* [Forking] You MUST call wait() now, it's not safe to rely on the destructor.

### Fixed

* [SharedMemory] Ensure memory is deleted when we're finished.

--------

## 2.1.0 - 2017-03-04

### Added

* [Forking] Made the wait() method chainable.

### Fixed

* [SharedMemory] Avoid creating two instances for the same memory.
* [Exceptions] Allow more exceptions to be added after initial retrieval.

--------

## 2.0.0 - 2016-10-16

### Added

* [Forking] Allow code to be executed even if pcntl isn't available.
* [Forking] Allow any adapter to be used to handle code execution.
* [Support] Added support for PHP 7.0 and 7.1.

### Fixed

* [Exceptions] Catch the new \Throwable type to handle engine exceptions.

### Changed

* [Namespace] Moved from \duncan3dc\Helpers to \duncan3dc\Forker to avoid clashes with other helpers.
* [Forking] The call() method now accepts variadic arguments.
* [Exceptions] Throw a more specific Exception (\duncan3dc\Forker\Exception) when things go wrong.
* [Exceptions] Removed the $ignoreErrors feature in favour of calling code catching the exception.
* [Support] Dropped support for PHP 5.

--------
