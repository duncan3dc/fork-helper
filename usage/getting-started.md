---
layout: default
title: Getting Started
permalink: /usage/getting-started/
api: Fork
---

To run some code in a thread just pass a [callable](http://php.net/manual/en/language.types.callable.php) to the `call()` method:

```php
$fork->call("phpinfo");
```

The code should start executing right away, and pass control back to your script. Here's a simple example demonstrating how the code runs side by side:

```php
$fork->call(function () {
    for ($i = 1; $i <= 3; $i++) {
        echo "Process A - " . $i . "\n";
        sleep(1);
    }
});
$fork->call(function () {
    for ($i = 1; $i < 3; $i++) {
        echo "Process B - " . $i . "\n";
        sleep(1);
    }
});

sleep(1);
echo "Waiting for the threads to finish...\n";
$fork->wait();
echo "End\n";
```

This should output something similar to the below:

```
Process A - 1
Process B - 1
Waiting for the threads to finish...
Process A - 2
Process B - 2
Process A - 3
End
```


The `call()` method supports [variadic arguments](http://php.net/manual/en/functions.arguments.php#functions.variable-arg-list):

```php
$fork->call("doStuffThatTakesALongTime", "5897", time());

# Is the same as:
doStuffThatTakesALongTime("5897", time());

```
