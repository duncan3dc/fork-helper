---
layout: default
title: Adapters - Interface
permalink: /adapters/interface/
api: AdapterInterface
---

The actual execution of code is handled by adapters. The library ships with 2 by default ([PCNTL](../pcntl/) and [Single Thread](../single-thread/)).  

You can create your own adapter by implementing the AdapterInterface and injecting it into the `Fork` instance:

```php
$fork = new Fork(new MyCustomAdapter);
```

There are three important things to keep in mind when building a custom adapter:
* The `call()` method must return a unique PID each time it is called.
* The `call()` method must catch any `\Throwable` and log that this PID failed.
* The `wait()` method must return a status greater than zero if something went wrong with the requested PID.
