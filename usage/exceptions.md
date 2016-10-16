---
layout: default
title: Error Handling
permalink: /usage/exceptions/
api: Fork
---

All exceptions thrown by forked code are caught and their basic details are stored. Then when the [wait()](../waiting/) method is called, a `\duncan3dc\Forker\Exception` is thrown.

```php
$fork->call(function () {
    throw new \RuntimeException("Something went wrong");
});
$fork->call(function () {
    throw new \DomainException("More problems ¯\_(ツ)_/¯");
});

$fork->wait();
```

Will output something like:
```
PHP Fatal error:  Uncaught duncan3dc\Forker\Exception: An error occurred within a thread, the return code was: 256
  - RuntimeException: Something went wrong (/tmp/fork-helper/test.php:9)
  - DomainException: More problems ¯\_(ツ)_/¯ (/tmp/fork-helper/test.php:12)
 in /tmp/fork-helper/src/Fork.php:95
Stack trace:
#0 /tmp/fork-helper/test.php(15): duncan3dc\Forker\Fork->wait()
#1 {main}
  thrown in /tmp/fork-helper/src/Fork.php on line 95
```
