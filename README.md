PHP Process Pool
================

[![Latest Version](https://img.shields.io/github/release/andersondanilo/process-pool.svg?style=flat-square)](https://github.com/andersondanilo/process-pool/releases)
![CI](https://github.com/andersondanilo/process-pool/workflows/CI/badge.svg)

PHP Process Pool is a simple process pool using symfony process

```php
use ProcessPool\ProcessPool;
use Symfony\Component\Process\Process;

function processGenerator($count) {
    for ($i = 0; $i < 10; $i++) {
        yield new Process("sleep $i");
    }
}

$processes = processGenerator(10);
$pool = new ProcessPool($processes);
$pool->setConcurrency(2);
$pool->wait();
```
