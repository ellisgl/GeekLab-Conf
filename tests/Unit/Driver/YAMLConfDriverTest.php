<?php

namespace Tests\Unit\Driver;

use GeekLab\Conf\Driver\YAMLConfDriver;

class YAMLConfDriverTest extends BaseDriverTestCase
{
    public function testDriver(): void
    {
        // Where the configurations are.
        $confDir = __DIR__ . '/../../_data/YAML/';
        $driver = new YAMLConfDriver($confDir . 'system.yaml', $confDir);
        $this->assertSame($this->expected, $driver->parseConfigurationFile());

        $expected = [
            'outofsection' => 456,
            'database'     =>
                [
                    'dsn'  => 'mysql:host=@[database.host];dbname=@[database.db]',
                    'host' => 'localhost',
                    'user' => 'dev',
                    'pass' => 'devpass',
                    'db'   => 'GeekLab'
                ],
            'devstuff'     =>
                [
                    'x' => 'something'
                ]
        ];

        $this->assertSame($expected, $driver->parseConfigurationFile('dev'));
    }
}
