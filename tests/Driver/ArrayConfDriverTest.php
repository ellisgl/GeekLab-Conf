<?php

use GeekLab\Conf\Driver\ArrayConfDriver;
use PHPUnit\Framework\TestCase;

class ArrayConfDriverTest extends TestCase
{
    public function testDriver(): void
    {
        // Where the configurations are.
        $confDir = __DIR__ . '/../_data/Array/';
        $driver                 = new ArrayConfDriver($confDir . 'system.php', $confDir);

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
            'outofsection' => 456,
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
