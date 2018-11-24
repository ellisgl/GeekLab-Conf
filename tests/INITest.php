<?php

use GeekLab\Configuration;
use PHPUnit\Framework\TestCase;

class ConfINI extends TestCase
{
    /**
     * @var Configuration\INI $configuration
     */
    protected static $configuration;

    // Set this up once for all the tests in side this.
    public static function setUpBeforeClass()
    {
        // Let's get less descriptive.
        define('DS', DIRECTORY_SEPARATOR);

        // Load in a the main INI with just Webapp and Dev.
        // Main INI file.
        $systemFile = __DIR__ . DS . 'data' . DS . 'ini' . DS . 'system.ini';

        // Where configuration INIs are.
        $configurationDirectory = __DIR__ . DS . 'data' . DS . 'ini' . DS . 'configurations' . DS;

        // Let's get loaded.
        self::$configuration = new GeekLab\Configuration\INI($systemFile, $configurationDirectory);
        self::$configuration->load();

        // Sometimes you just want to see it.
        //var_dump(self::$configuration->getAll());
    }

    /** @test */
    public function testThatItIsAnObject()
    {
        $this->assertTrue(is_object(self::$configuration), 'INI is not an object!');
    }

    /** @test */
    public function testThatItImplementsConfigurationInterface()
    {
        $this->assertInstanceOf('GeekLab\Configuration\ConfigurationInterface', self::$configuration, 'Configuration\INI does not implement Configuration\ConfigurationInterface!');
    }

    /** @test */
    public function testThatItExtendsConfigurationAbstract()
    {
        $this->assertInstanceOf('GeekLab\Configuration\ConfigurationAbstract', self::$configuration, 'Configuration\INI does not an instance of Configuration\ConfigurationAbstract!');
    }

    /** @test */
    public function testGetAllReturnsArray()
    {
        // Get compiled config. Is it an array?
        $this->assertTrue(is_array(self::$configuration->getAll()), 'Configuration\INI->getAll() did not return an array!');
    }

    /** @test */
    public function testGetBasic()
    {
        // Basic get.
        $this->assertEquals('CrazyWebApp', self::$configuration->get('SERVICE'), 'Configuration\INI->get() "Basic" failed!');
    }

    /** @test */
    public function testGetIsCaseInsensitive()
    {
        // Make sure it case doesn't matter.
        $this->assertEquals('CrazyWebApp', self::$configuration->get('SeRvIcE'), 'Configuration\INI->get() case change failed!');
    }

    /** @test */
    public function testGetWorksWithDotNotation()
    {
        // Test dot notation.
        $this->assertEquals('localhost', self::$configuration->get('database.host'), 'Configuration\INI->get() dot notation failed!');
    }

    /** @test */
    public function testThatSpacesWhereConvertedToUnderScores()
    {
        // Test spaces to underscore.
        $this->assertEquals('space pants', self::$configuration->get('space_pants.look_at_my'), 'Configuration\INI did not change spaces in keys to underscores!');
    }

    /** @test */
    public function testThatPeriodsWhereConvertedToUnderScores()
    {
        // Test periods to underscore.
        $this->assertEquals('And that is a fact!', self::$configuration->get('other_stuff._i_like_dots_period'), 'Configuration\INI) did not change periods in keys to underscores!');
    }

    /** @test */
    public function testGetCanReturnArray()
    {
        // Test getting just an array from key.
        $sArr = array('LOOK_AT_MY' => 'space pants');
        $this->assertTrue(is_array(self::$configuration->get('space_pants')), 'Configuration\INI->get() did not return an array for a "section"!');
        $this->assertEquals($sArr, self::$configuration->get('space_pants'), 'Configuration\INI->get() did not return expected array for a "section"!');
    }

    /** @test */
    public function testThatItCanMerge()
    {
        // Test the merging.
        $this->assertEquals('something', self::$configuration->get('devstuff.x'), 'Configuration\INI did not properly merge values!');
        $this->assertEquals('ellisgl', self::$configuration->get('database.user'), 'Configuration\INI did not properly merge values!');
    }

    /** @test */
    public function testThatItCanReplaceSelfReferencedPlaceholders()
    {
        // Test the self referenced placeholder replacement.
        $this->assertEquals('mysql:host=localhost;dbname=ellisgldb', self::$configuration->get('database.dsn'), 'Configuration\INI did not replace self referenced placeholders!');
    }

    /** @test */
    public function testThatItDoesntReplaceMissingSelfReferencedPlaceholders()
    {
        // Make sure we do not replace things we can't reference.
        $this->assertEquals('@[doesnotexist]', self::$configuration->get('somestuff.d'), 'Configuration\INI replaced a non existing self reference.');
    }

    /** @test */
    public function testThatItCanReplaceRecursiveSelfReferencedPlaceholders()
    {
        // Test the recursive self referenced placeholder replacement.
        $this->assertEquals('We Can Do That!', self::$configuration->get('selfreferencedplaceholder.a'), 'Configuration\INI did not replace the recursive self referenced placeholder!');
        $this->assertEquals('And this!', self::$configuration->get('selfreferencedplaceholder.b'), 'Configuration\INI did not replace the recursive self referenced placeholder!');
        $this->assertEquals('This too!', self::$configuration->get('selfreferencedplaceholder.c'), 'Configuration\INI did not replace the recursive self referenced placeholder!');
    }

    /** @test */
    public function testThatItDoesntReplaceMissingRecursiveSelfReferencedPlaceholders()
    {
        // Make sure we do not replace things we can't reference.
        $this->assertEquals('@[doesnt].@[exist]', self::$configuration->get('somestuff.e'), 'Configuration\INI replaced a non existing recursive self reference.');
        $this->assertEquals('@[doesnt.@[exist]]', self::$configuration->get('somestuff.f'), 'Configuration\INI replaced a non existing recursive self reference.');
        $this->assertEquals('@[@[doesnt].exist]', self::$configuration->get('somestuff.g'), 'Configuration\INI replaced a non existing recursive self reference.');
        $this->assertEquals('@[@[doesnt].@[exist]]', self::$configuration->get('somestuff.h'), 'Configuration\INI replaced a non existing recursive self reference.');
    }

    /** @test */
    public function testThatItCanReplaceEnvironmentVariablePlaceholders()
    {
        // Test environment variable replacement.
        $this->assertEquals('utf8', self::$configuration->get('database.charset'), 'Configuration\INI did not replace an environment variable placeholder.');
    }

    /** @test */
    public function testThatItCanReplaceEnvironmentVariablePlaceholderWithReferencedPlaceholders()
    {
        // Check to see if we can replace a environment variable placeholder that uses a self referenced placeholder inside of it.
        $this->assertEquals('utf8', self::$configuration->get('somestuff.k'), 'Configuration\INI did not replace an environment variable placeholder with a self referenced placeholder inside it.');
    }

    /** @test */
    public function testThatItDoesntReplaceMissingEnvironmentVariablePlaceholders()
    {
        $this->assertEquals('$[DOESNOTEXIST]', self::$configuration->get('somestuff.i'), 'Configuration\INI replaced a non existing recursive self reference.');
    }
}
