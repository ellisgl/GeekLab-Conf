[![License: BSD](https://img.shields.io/badge/License-BSD-yellow.svg)](https://opensource.org/licenses/BSD-3-Clause)
[![phpstan enabled](https://img.shields.io/badge/phpstan-enabled-green.svg)](https://github.com/phpstan/phpstan)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ellisgl/GeekLab-Conf/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ellisgl/GeekLab-Conf/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/ellisgl/GeekLab-Conf/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ellisgl/GeekLab-Conf/?branch=master)

# geeklab/conf
Immutable configuration system loader & parser for PHP >= 7.2 that supports multiple file formats and has some templating features.
This library is an alternative to '.env' type configuration libraries and uses the [Strategy Pattern](https://designpatternsphp.readthedocs.io/en/latest/Behavioral/Strategy/README.html). 

# [Benchmarks](https://github.com/ellisgl/php-benchmarks/blob/master/results/Confs.md)

## Latest
2.0.4 (2019/06/22): Fixing code complexity, PHPStan complaints and other possible issues.

## Features:
* Multi-file configuration loading, no more monolithic configurations!
* Self referencing placeholders. @[X.Y.Z]
* Recursive self referencing placeholders. @[@[X.Y.Z].SOME_KEY]
* Environment variable placeholders. $[ENVIRONMENT_VARIABLE_NAME] (PHP likes "${YOUR_TEXT_HERE}" a little too much...)
* Can use INI, JSON, YAML and Array files.
* Immutability, since you shouldn't change your configuration during run time.

## Installation:
composer require geeklab/conf

## Usage:
Basic:
```PHP
// Where the configurations are.
$configurationDirectory = __DIR__ . '/config/';

// Load Configuration system with the JSON Configuration Driver 
$configuration = new GLConf(
    new JSONConfDriver(
        $configurationDirectory . 'system.json',  // Path and file name of main (top level) configuration.
        $configurationDirectory                   // Path to the other configuation files. 
    )
);

$configuration->init();

// Get the whole configuration.
var_export($configuration->getAll());

// Get one item.
var_export($configuration->get('space_pants.look_at_my'));
```

Detailed:
* [INI](/docs/INI.md)
* [Array](/docs/Array.md)
* [JSON](/docs/JSON.md)
* [YAML](/docs/YAML.md)

PSR Compliance:
* PSR-1
* PSR-2
* PSR-4

## Todo:
* More Documentation.
* Benchmarks.

