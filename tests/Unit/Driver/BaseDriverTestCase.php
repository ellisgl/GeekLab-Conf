<?php

namespace Tests\Unit\Driver;

use PHPUnit\Framework\TestCase;

class BaseDriverTestCase extends TestCase
{
    /**
     * A common expected result.
     *
     * @var array
     */
    protected array $expected = [
        'service' => 'CrazyWebApp',
        'env'     => 'dev',
        'conf'    =>
            [
                'webapp',
                'dev',
                'ellisgl'
            ]
    ];
}
