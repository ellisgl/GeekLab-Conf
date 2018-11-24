[![Build Status](https://travis-ci.com/ellisgl/GeekLab-Conf.svg?branch=master)](https://travis-ci.com/ellisgl/GeekLab-Conf)

# geeklab/conf
Immutable configuration system for PHP >= 7.1

## Features:
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

## Todo:
* More Documentation.

## Change Log
1.0.0 (2018/11/23): Initial release.
