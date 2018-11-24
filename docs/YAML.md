### Usage
Setup a primary YAML (system.yaml, main.yaml, moneky.yaml or what ever you want to call it). See [/tests/data/ini/system.yaml](/tests/data/ini/system.yaml) for an example.

This base file does not have any sections (but it could). The important property is `conf[]`, this tells the loader which INI files to load up, and this is put in order, since we are merging/combining/replacing stuff from the previous imports.

Setup your secondary INIs (E.g. webapp.yaml, dev.yaml, elllisgl.yaml, etc...). See [/tests/data/yaml](/tests/data/yaml) for examples.

_note_: While you can use spaces and periods in sections / properties, just remember that spaces and periods will be transformed into underscores `_`.

You'd probably want these in another directory, like /conf. also don't forget to add `*.yaml` to your `.gitignore`, since you really don't want your configurations out there in wild! 

You might notice something in the `dev.yaml`, `@[%database.host]` and `@[database.db%]`.

These are self referenced placeholders. When the configuration is being compiled, the last step is to replace those placeholders with something from with in the configuration. `@[data.host]` would be filled with `localhost` and  `@[database.db]` would be replaced with `ellisgldb`, since the last INI `ellisgl.yaml` imported had database -> db set to `ellisgldb` it overwrote the value that came from `dev.yaml`.

Also in the `ellisgl.yaml`, there is a `$[DB_CHARSET]` placeholder which will be replaced by the contents of the environmental variable `DB_CHARSET`.

You can also do a recursive self referenced placeholder replacement. If you look at the `ellisgl.yaml`, you might notice `a: '@[selfreferencedplaceholder.@[somestuff.a]]'` which would become `@[selfreferencedplaceholder.x]` and then finally become `We Can Do That!`. 

The next thing is to actually use it. So in your bootstrap.php, index.php, or what ever you load up first:

```PHP
<?php
require_once('/path/to/your/vendor/autoload.php');

// Main INI file.
$systemFile = '/path/to/your/main-system-monkey.yaml';

// Where configuration INIs are.
$confDir = '/path/to/your/yamls/';

// Start 'er up!
$conf = new GeekLab\Conf\YAML($systemFile, $confDir);
$conf->init();

// Do some things.
define('IS_DEV', ($conf->get('ENV') == 'dev') ? true : false);
$db = new PDO($conf->get('database.dsn'), $conf->get('database.user'), $conf->get('database.pass'));
```

So for `$conf->get()`, it uses dot notation to access the data, and everything is case insensitive. Also there is a `getAll()` method, which will return an array of the compiled config.