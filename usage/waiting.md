---
layout: default
title: Waiting
permalink: /usage/waiting/
api: Fork
---

After setting some code to run using `call()` it will be necessary at some point to wait for that code to finish.

To do this must call the `wait()` method:

```php
$fork->call("doStuffThatTakesALongTime", "5897");
$fork->call("doStuffThatTakesALongTime", "1048");

$fork->wait();
echo "We've done 5897 and 1048 now\n";

$fork->call("doStuffThatTakesALongTime", "8077");
$fork->call("doStuffThatTakesALongTime", "2222");

$fork->wait();
echo "We've done 8077 and 2222 now\n";
```


After calling `wait()` the fork instance is useless, if you want to handle code in batches, you'll need separate instances:
```php
$fork = new Fork;
$fork->call("doStuffThatTakesALongTime", "5897");
$fork->call("doStuffThatTakesALongTime", "1048");

$fork->wait();
echo "We've done 5897 and 1048 now\n";

$fork = new Fork;
$fork->call("doStuffThatTakesALongTime", "8077");
$fork->call("doStuffThatTakesALongTime", "2222");

$fork->wait();
echo "We've done 8077 and 2222 now\n";
```


You can wait for a specific thread using it's PID:

```php
$important = $fork->call("doImportantStuff");
$boring = $fork->call("doBoringStuff");

$fork->wait($important);
echo "Important stuff is done, boring stuff may still be going on\n";

$fork->wait($boring);
echo "Boring stuff is done\n";
```
