---
layout: default
title: Setup
permalink: /setup/
api: Fork
---

All classes are in the `duncan3dc\Forker` namespace.

By default all you need to do is create a new instance, and it will figure out whether to use PCNTL or not.

```php
require_once __DIR__ . "vendor/autoload.php";

use duncan3dc\Forker\Fork;

$fork = new Fork;
```
