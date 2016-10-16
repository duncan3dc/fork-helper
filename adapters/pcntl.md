---
layout: default
title: Adapters - PCNTL
permalink: /adapters/pcntl/
api: PcntlAdapter
---

This adapter is where the actual forking of code is handled, it requires the [PCNTL](http://php.net/manual/en/book.pcntl.php) and [Shared Memory](http://php.net/manual/en/book.shmop.php) extensions.  

Unfortunately PCNTL is not available on Windows, so this adapter will not work there.
