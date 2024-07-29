fork-helper
===========

Simple class to fork processes in PHP and allow multi-threading.  

Full documentation is available at https://duncan3dc.github.io/fork-helper/  
PHPDoc API documentation is also available at [https://duncan3dc.github.io/fork-helper/api/](https://duncan3dc.github.io/fork-helper/api/namespaces/duncan3dc.Forker.html)  

[![release](https://poser.pugx.org/duncan3dc/fork-helper/version.svg)](https://packagist.org/packages/duncan3dc/fork-helper)
[![build](https://github.com/duncan3dc/fork-helper/workflows/buildcheck/badge.svg?branch=main)](https://github.com/duncan3dc/fork-helper/actions?query=branch%3Amain+workflow%3Abuildcheck)
[![coverage](https://codecov.io/gh/duncan3dc/fork-helper/graph/badge.svg)](https://codecov.io/gh/duncan3dc/fork-helper)


Quick Example
-------------

Run some code asynchronously:
```php
$fork = new \duncan3dc\Forker\Fork;

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

_Read more at https://duncan3dc.github.io/fork-helper/_


Changelog
---------
A [Changelog](CHANGELOG.md) has been available since version 2.0.0


Where to get help
-----------------
Found a bug? Got a question? Just not sure how something works?  
Please [create an issue](https//github.com/duncan3dc/fork-helper/issues) and I'll do my best to help out.  
Alternatively you can catch me on [Twitter](https://twitter.com/duncan3dc)


## duncan3dc/fork-helper for enterprise

Available as part of the Tidelift Subscription

The maintainers of duncan3dc/fork-helper and thousands of other packages are working with Tidelift to deliver commercial support and maintenance for the open source dependencies you use to build your applications. Save time, reduce risk, and improve code health, while paying the maintainers of the exact dependencies you use. [Learn more.](https://tidelift.com/subscription/pkg/packagist-duncan3dc-fork-helper?utm_source=packagist-duncan3dc-fork-helper&utm_medium=referral&utm_campaign=readme)
