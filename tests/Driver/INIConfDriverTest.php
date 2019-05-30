<?php

use GeekLab\Conf\Driver\INIConfDriver;
use PHPUnit\Framework\TestCase;

class INIConfDriverTest extends TestCase
{
    public function testDriver(): void
    {
        // Where the configurations are.
        $configurationDirectory = __DIR__ . '/../_data/INI/';
        $driver                 = new INIConfDriver($configurationDirectory . 'system.ini', $configurationDirectory);

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
            'outofsection' => '456',
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
