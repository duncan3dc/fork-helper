fork-helper
===========

Simple class to fork processes in PHP and allow multi-threading.  

Full documentation is available at http://duncan3dc.github.io/fork-helper/  
PHPDoc API documentation is also available at [http://duncan3dc.github.io/fork-helper/api/](http://duncan3dc.github.io/fork-helper/api/namespaces/duncan3dc.Forker.html)  

[![Build Status](https://travis-ci.org/duncan3dc/fork-helper.svg?branch=master)](https://travis-ci.org/duncan3dc/fork-helper)
[![Latest Stable Version](https://poser.pugx.org/duncan3dc/fork-helper/version.svg)](https://packagist.org/packages/duncan3dc/fork-helper)


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

_Read more at http://duncan3dc.github.io/fork-helper/_  


Changelog
---------
A [Changelog](CHANGELOG.md) has been available since version 2.0.0


Where to get help
-----------------
Found a bug? Got a question? Just not sure how something works?  
Please [create an issue](https//github.com/duncan3dc/fork-helper/issues) and I'll do my best to help out.  
Alternatively you can catch me on [Twitter](https://twitter.com/duncan3dc)
