<?php

use GeekLab\Conf\Driver\JSONConfDriver;
use PHPUnit\Framework\TestCase;

class JSONConfDriverTest extends TestCase
{
    public function testDriver(): void
    {
        // Where the configurations are.
        $configurationDirectory = __DIR__ . '/../_data/JSON/';
        $driver                 = new JSONConfDriver($configurationDirectory . 'system.json', $configurationDirectory);

        $expected = [
            'service' => 'CrazyWebApp',
            'env'     => 'dev',
            'conf'    =>
                [
                    'webapp',
                    'dev',
                    'ellisgl'
                ]
        ];

        $this->assertSame($expected, $driver->parseConfigurationFile());

        $expected = [
            'database' =>
                [
                    'dsn'  => 'mysql:host=@[database.host];dbname=@[database.db]',
                    'host' => 'localhost',
                    'user' => 'dev',
                    'pass' => 'devpass',
                    'db'   => 'GeekLab'
                ],
            'devstuff' =>
                [
                    'x' => 'something'
                ]
        ];

        $this->assertSame($expected, $driver->parseConfigurationFile('dev'));
    }
}
