[![Build Status](https://travis-ci.com/ellisgl/GeekLab-Conf.svg?branch=master)](https://travis-ci.com/ellisgl/GeekLab-Conf)

# geeklab/conf
Immutable configuration system loader / parser for PHP >= 7.1 that support multiple file formats and has some "templating" features.

## Features:
* Multi-file configuration loading, no more monolithic configurations!
* Self referencing placeholders. @[X.Y.Z]
* Recursive self referencing placeholders. @[@[X.Y.Z].SOME_KEY]
* Environment variable placeholders. $[ENVIRONMENT_VARIABLE_NAME] (PHP likes "${YOUR_TEXT_HERE}" a little too much...)
* Can use INI, JSON, YAML and Array files. 

## Installation:
composer require geeklab/conf

## Usage:
* [INI](/docs/INI.md)
* [Array](/docs/Array.md)
* [JSON](/docs/JSON.md)
* [YAML](/docs/YAML.md)

## Extending:
* TODO

PSR Compliance:
* PSR-1
* PSR-2
* PSR-4

## Benchmarks (If you know of a library like this one, I would like to compare it.)

* Revs: 100
* Iterations: 100

| subject | mem_peak | best      | mean      | mode      | worst     | stdev    | rstdev | diff  |
|---------|----------|-----------|-----------|-----------|-----------|----------|--------|-------|
| JSON    | 787,768b | 529.100μs | 535.753μs | 532.735μs | 548.860μs | 4.395μs  | 0.82%  | 1.00x |
| YAML    | 787,240b | 586.760μs | 598.836μs | 595.435μs | 694.880μs | 15.215μs | 2.54%  | 1.12x |
| INI     | 787,288b | 592.530μs | 602.118μs | 598.577μs | 716.130μs | 17.531μs | 2.91%  | 1.12x |
| Arr     | 786,912b | 607.050μs | 614.357μs | 612.415μs | 701.590μs | 9.938μs  | 1.62%  | 1.15x |

## Todo:
* More Documentation.

## Change Log
1.0.0 (2018/11/23): Initial release.
