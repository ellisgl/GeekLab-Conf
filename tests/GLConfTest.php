<?php

use GeekLab\Conf\GLConf;
use GeekLab\Conf\Driver\ArrayConfDriver;
use GeekLab\Conf\Driver\JSONConfDriver;
use GeekLab\Conf\Driver\INIConfDriver;
use GeekLab\Conf\Driver\YAMLConfDriver;
use PHPUnit\Framework\TestCase;

class GLConfTest extends TestCase
{
    private function runRepeatableTests(GLConf $configuration): void
    {
        // Get compiled config. Is it an array?
        $this->assertTrue(is_array($configuration->getAll()), 'GLConf::getAll did not return an array!');

        //var_export($configuration->getAll());

        // Basic get.
        $this->assertEquals('CrazyWebApp', $configuration->get('SERVICE'), 'GLConf::get "Basic" failed!');

        // Make sure it case doesn't matter.
        $this->assertEquals('CrazyWebApp', $configuration->get('SeRvIcE'), 'GLConf::get case change failed!');

        // Test dot notation.
        $this->assertEquals('localhost', $configuration->get('database.host'), 'GLConf::get dot notation failed!');

        // Test spaces to underscore.
        $this->assertEquals('space pants', $configuration->get('space_pants.look_at_my'), 'GLConf::conformArray did not change spaces in keys to underscores!');

        // Test periods to underscore.
        $this->assertEquals('And that is a fact!', $configuration->get('other_stuff._i_like_dots_period'), 'GLConf::conformArray did not change periods in keys to underscores!');

        // Test getting just an array from key.
        $sArr = array('LOOK_AT_MY' => 'space pants');
        $this->assertTrue(is_array($configuration->get('space_pants')), 'GLConf::get did not return an array for a "section"!');
        $this->assertEquals($sArr, $configuration->get('space_pants'), 'GLConf::get did not return expected array for a "section"!');

        // Test the merging.
        $this->assertEquals('something', $configuration->get('devstuff.x'), 'GLConf::init did not properly merge values!');
        $this->assertEquals('ellisgl', $configuration->get('database.user'), 'GLConf::init did not properly merge values!');

        // Test the self referenced placeholder replacement.
        $this->assertEquals('mysql:host=localhost;dbname=ellisgldb', $configuration->get('database.dsn'), 'GLConf::init did not replace self referenced placeholders!');

        // Make sure we do not replace things we can't reference.
        $this->assertEquals('@[doesnotexist]', $configuration->get('somestuff.d'), 'GLConf::init replaced a non existing self reference.');

        // Test the recursive self referenced placeholder replacement.
        $this->assertEquals('We Can Do That!', $configuration->get('selfreferencedplaceholder.a'), 'GLConf::init did not replace the recursive self referenced placeholder!');
        $this->assertEquals('And this!', $configuration->get('selfreferencedplaceholder.b'), 'GLConf::init did not replace the recursive self referenced placeholder!');
        $this->assertEquals('This too!', $configuration->get('selfreferencedplaceholder.c'), 'GLConf::init did not replace the recursive self referenced placeholder!');

        // Test we do not replace things we can't reference.
        $this->assertEquals('@[doesnt].@[exist]', $configuration->get('somestuff.e'), 'GLConf::init replaced a non existing recursive self reference.');
        $this->assertEquals('@[doesnt.@[exist]]', $configuration->get('somestuff.f'), 'GLConf::init replaced a non existing recursive self reference.');
        $this->assertEquals('@[@[doesnt].exist]', $configuration->get('somestuff.g'), 'GLConf::init replaced a non existing recursive self reference.');
        $this->assertEquals('@[@[doesnt].@[exist]]', $configuration->get('somestuff.h'), 'GLConf::init replaced a non existing recursive self reference.');

        // Test environment variable replacement.
        $this->assertEquals('utf8', $configuration->get('database.charset'), 'GLConf::init did not replace an environment variable placeholder.');

        // Test we do not replace unknown environment variables.
        $this->assertEquals('$[DOESNOTEXIST]', $configuration->get('somestuff.i'), 'GLConf::init replaced a non existing recursive self reference.');
    }

    public function testArrayConfiguration(): void
    {
        // Where the configurations are.
        $configurationDirectory = __DIR__ . '/_data/Array/';
        $configuration = new GLConf(new ArrayConfDriver($configurationDirectory . 'system.php', $configurationDirectory));

        $configuration->init();

        $this->runRepeatableTests($configuration);
    }

    public function testINIConfiguration(): void
    {
        // Where the configurations are.
        $configurationDirectory = __DIR__ . '/_data/INI/';
        $configuration = new GLConf(new INIConfDriver($configurationDirectory . 'system.ini', $configurationDirectory));

        $configuration->init();

        $this->runRepeatableTests($configuration);
    }

    public function testJSONConfiguration(): void
    {
        // Where the configurations are.
        $configurationDirectory = __DIR__ . '/_data/JSON/';
        $configuration = new GLConf(new JSONConfDriver($configurationDirectory . 'system.json', $configurationDirectory));

        $configuration->init();

        $this->runRepeatableTests($configuration);
    }

    public function testYAMLConfiguration(): void
    {
        // Where the configurations are.
        $configurationDirectory = __DIR__ . '/_data/YAML/';
        $configuration = new GLConf(new YAMLConfDriver($configurationDirectory . 'system.yaml', $configurationDirectory));

        $configuration->init();

        $this->runRepeatableTests($configuration);
    }

}