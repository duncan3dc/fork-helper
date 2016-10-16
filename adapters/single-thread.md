---
layout: default
title: Adapters - Single Thread
permalink: /adapters/single-thread/
api: SingleThreadAdapter
---

This is used to gracefully degrade when the [PCNTL adapter](../pcntl/) cannot be used.

As the name suggests, all code is run in a single thread and each `call()` will block until the requested code has executed.
