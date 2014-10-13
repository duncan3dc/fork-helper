fork-helper
===========

Simple class to fork processes in PHP and allow multi-threading

[![Build Status](https://travis-ci.org/duncan3dc/fork-helper.svg?branch=master)](https://travis-ci.org/duncan3dc/fork-helper)
[![Latest Stable Version](https://poser.pugx.org/duncan3dc/fork-helper/version.svg)](https://packagist.org/packages/duncan3dc/fork-helper)



Public Methods
--------------
* call(callable $func): int - Calls the specified function in a new thread, and returns the pid of the created thread.
* wait(): int - Waits for the threads created by the call() method to finish. Returns 0 if all threads completed succesfully, otherwise it will return the exit code of an failed thread.


Public Properties
-----------------
* ignoreErrors: boolean - False by default, this will cause the wait() method to throw an exception for any threads with an exit status above 0. If this property is set to true then wait() will not throw an exception but just return the exit status of a failed thread.


Examples
--------

```php
$fork = new \duncan3dc\Helpers\Fork;

$fork->call(function() {
	for($i = 1; $i <= 3; $i++) {
		echo "Process A - " . $i . "\n";
		sleep(1);
	}
});
$fork->call(function() {
	for($i = 1; $i < 3; $i++) {
		echo "Process B - " . $i . "\n";
		sleep(1);
	}
});

sleep(1);
echo "Waiting for the threads to finish...\n";
$fork->wait();
echo "End\n";
```
