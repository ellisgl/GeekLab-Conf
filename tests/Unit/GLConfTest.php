<?php

namespace Tests\Unit;

use GeekLab\Conf\GLConf;
use GeekLab\Conf\Driver\ArrayConfDriver;
use GeekLab\Conf\Driver\JSONConfDriver;
use GeekLab\Conf\Driver\INIConfDriver;
use GeekLab\Conf\Driver\YAMLConfDriver;
use PHPUnit\Framework\TestCase;

class GLConfTest extends TestCase
{
    private function runRepeatableTests(GLConf $conf): void
    {
        // Get compiled config. Is it an array?
        $this->assertIsArray($conf->getAll(), 'GLConf::getAll did not return an array!');

        // Basic get.
        $this->assertEquals('CrazyWebApp', $conf->get('SERVICE'), 'GLConf::get "Basic" failed!');

        // Make sure it's case doesn't matter.
        $this->assertEquals('CrazyWebApp', $conf->get('SeRvIcE'), 'GLConf::get case change failed!');

        // Test dot notation.
        $this->assertEquals('localhost', $conf->get('database.host'), 'GLConf::get dot notation failed!');

        // Test spaces to underscore.
        $this->assertEquals(
            'space pants',
            $conf->get('space_pants.look_at_my'),
            'GLConf::conformArray did not change spaces in keys to underscores!'
        );

        // Test periods to underscore.
        $this->assertEquals(
            'And that is a fact!',
            $conf->get('other_stuff._i_like_dots_period'),
            'GLConf::conformArray did not change periods in keys to underscores!'
        );

        // Test getting just an array from key.
        $sArr = array('LOOK_AT_MY' => 'space pants');
        $this->assertIsArray($conf->get('space_pants'), 'GLConf::get did not return an array for a "section"!');
        $this->assertEquals(
            $sArr,
            $conf->get('space_pants'),
            'GLConf::get did not return expected array for a "section"!'
        );

        // Test the merging.
        $this->assertEquals('something', $conf->get('devstuff.x'), 'GLConf::init did not properly merge values!');
        $this->assertEquals('ellisgl', $conf->get('database.user'), 'GLConf::init did not properly merge values!');

        // Test the self referenced placeholder replacement.
        $this->assertEquals(
            'mysql:host=localhost;dbname=ellisgldb',
            $conf->get('database.dsn'),
            'GLConf::init did not replace self referenced placeholders!'
        );

        // Make sure we do not replace things we can't reference.
        $this->assertEquals(
            '@[doesnotexist]',
            $conf->get('somestuff.d'),
            'GLConf::init replaced a non existing self reference.'
        );

        // Test the recursive self referenced placeholder replacement.
        $this->assertEquals(
            'We Can Do That!',
            $conf->get('selfreferencedplaceholder.a'),
            'GLConf::init did not replace the recursive self referenced placeholder!'
        );
        $this->assertEquals(
            'And this!',
            $conf->get('selfreferencedplaceholder.b'),
            'GLConf::init did not replace the recursive self referenced placeholder!'
        );
        $this->assertEquals(
            'This too!',
            $conf->get('selfreferencedplaceholder.c'),
            'GLConf::init did not replace the recursive self referenced placeholder!'
        );

        // Test we do not replace things we can't reference.
        $this->assertEquals(
            '@[doesnt].@[exist]',
            $conf->get('somestuff.e'),
            'GLConf::init replaced a non existing recursive self reference.'
        );
        $this->assertEquals(
            '@[doesnt.@[exist]]',
            $conf->get('somestuff.f'),
            'GLConf::init replaced a non existing recursive self reference.'
        );
        $this->assertEquals(
            '@[@[doesnt].exist]',
            $conf->get('somestuff.g'),
            'GLConf::init replaced a non existing recursive self reference.'
        );
        $this->assertEquals(
            '@[@[doesnt].@[exist]]',
            $conf->get('somestuff.h'),
            'GLConf::init replaced a non existing recursive self reference.'
        );

        // Test environment variable replacement.
        $this->assertEquals(
            'utf8',
            $conf->get('database.charset'),
            'GLConf::init did not replace an environment variable placeholder.'
        );

        // Test we do not replace unknown environment variables.
        $this->assertEquals(
            '$[DOESNOTEXIST]',
            $conf->get('somestuff.i'),
            'GLConf::init replaced a non existing recursive self reference.'
        );

        // Test we striped out the 'out of section' settings.
        $this->assertEmpty($conf->get('outofsection'), 'GLConf::init did not strip out of section items.');
    }

    public function testArrayConfiguration(): void
    {
        // Where the configurations are.
        $confDir = __DIR__ . '/../_data/Array/';
        $conf = new GLConf(new ArrayConfDriver($confDir . 'system.php', $confDir));
        $conf->init();

        $this->runRepeatableTests($conf);
    }

    public function testINIConfiguration(): void
    {
        // Where the configurations are.
        $confDir = __DIR__ . '/../_data/INI/';
        $conf = new GLConf(new INIConfDriver($confDir . 'system.ini', $confDir));
        $conf->init();

        $this->runRepeatableTests($conf);
    }

    public function testInjectedValues(): void
    {
        $confDir = __DIR__ . '/../_data/Array/';
        $conf = new GLConf(
            new ArrayConfDriver($confDir . 'system.php', $confDir),
            ['doesnotexist' => '127.0.0.1']
        );
        $conf->init();

        $this->assertEquals(
            '127.0.0.1',
            $conf->get('somestuff.d'),
            'GLConf::init replaced a non existing recursive self reference.'
        );
    }

    public function testJSONConfiguration(): void
    {
        // Where the configurations are.
        $confDir = __DIR__ . '/../_data/JSON/';
        $conf = new GLConf(new JSONConfDriver($confDir . 'system.json', $confDir));
        $conf->init();

        $this->runRepeatableTests($conf);
    }

    public function testKeyLowerCaseOption(): void
    {
        // Where the configurations are.
        $confDir = __DIR__ . '/../_data/Array/';
        $conf = new GLConf(new ArrayConfDriver($confDir . 'system.php', $confDir), [], ['keys_lower_case']);
        $conf->init();

        $dbConf = $conf->get('DATABASE');
        $this->assertArrayHasKey('host', $dbConf);
        $this->assertArrayNotHasKey('HOST', $dbConf);
    }

    public function testKeySameCaseOption(): void
    {
        // Where the configurations are.
        $confDir = __DIR__ . '/../_data/Array/';
        $conf = new GLConf(new ArrayConfDriver($confDir . 'system.php', $confDir), [], ['keys_same_case']);
        $conf->init();
        $someConf = $conf->get('space_Pants');
        $this->assertArrayHasKey('look_at_my', $someConf);
        $this->assertArrayNotHasKey('LOOK_AT_MY', $someConf);

        $this->assertFalse($conf->get('space_pants'));
    }

    public function testKeyUpperCaseOption(): void
    {
        // Where the configurations are.
        $confDir = __DIR__ . '/../_data/Array/';
        $conf = new GLConf(new ArrayConfDriver($confDir . 'system.php', $confDir), [], ['keys_upper_case']);
        $conf->init();

        $dbConf = $conf->get('database');
        $this->assertArrayHasKey('HOST', $dbConf);
        $this->assertArrayNotHasKey('host', $dbConf);
    }

    public function testYAMLConfiguration(): void
    {
        // Where the configurations are.
        $confDir = __DIR__ . '/../_data/YAML/';
        $conf = new GLConf(new YAMLConfDriver($confDir . 'system.yaml', $confDir));
        $conf->init();

        $this->runRepeatableTests($conf);
    }
}
