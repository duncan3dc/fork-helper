---
layout: default
title: Waiting
permalink: /usage/waiting/
api: Fork
---

After setting some code to run using `call()` it will be nessecary at some point to wait for that code to finish.

When you're done with your `Fork` instance it will automatically wait for all threads to finish:

```php
$fork->call("doStuffThatTakesALongTime");

# PHP will block here and wait for doStuffThatTakesALongTime() to return
unset($fork);
```


You can choose when to wait for all threads using the `wait()` method:

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


You can wait for a specific thread using it's PID:

```php
$important = $fork->call("doImportantStuff");
$boring = $fork->call("doBoringStuff");

$fork->wait($important);
echo "Important stuff is done, boring stuff may still be going on\n";

$fork->wait($boring);
echo "Boring stuff is done\n";
```
