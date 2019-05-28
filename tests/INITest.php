<?php

use GeekLab\Conf;
use PHPUnit\Framework\TestCase;

class INITest extends TestCase
{
    /** @var Conf\INI $configuration */
    protected static $configuration;

    // Set this up once for all the tests in side this.
    public static function setUpBeforeClass(): void
    {
        // Load in a the main INI configuration.
        // Where the configurations are.
        $configurationDirectory = __DIR__ . '/data/ini/';

        // Main INI file.
        $systemFile = $configurationDirectory . 'system.ini';

        // Let's get loaded.
        self::$configuration = new Conf\INI($systemFile, $configurationDirectory);
        self::$configuration->init();

        // Sometimes you just want to see it.
        //var_dump(self::$configuration->getAll());
    }

    public function testThatItIsAnObject(): void
    {
        $this->assertTrue(is_object(self::$configuration), 'INI is not an object!');
    }

    public function testThatItImplementsConfigurationInterface(): void
    {
        $this->assertInstanceOf('GeekLab\Conf\ConfInterface', self::$configuration, 'Conf\INI does not implement Conf\ConfnInterface!');
    }

    public function testThatItExtendsConfigurationAbstract(): void
    {
        $this->assertInstanceOf('GeekLab\Conf\ConfAbstract', self::$configuration, 'Conf\INI does not an instance of Conf\ConfAbstract!');
    }

    public function testGetAllReturnsArray(): void
    {
        // Get compiled config. Is it an array?
        $this->assertTrue(is_array(self::$configuration->getAll()), 'Conf\INI->getAll() did not return an array!');
    }

    public function testGetBasic(): void
    {
        // Basic get.
        $this->assertEquals('CrazyWebApp', self::$configuration->get('SERVICE'), 'Conf\INI->get() "Basic" failed!');
    }

    public function testGetIsCaseInsensitive(): void
    {
        // Make sure it case doesn't matter.
        $this->assertEquals('CrazyWebApp', self::$configuration->get('SeRvIcE'), 'Conf\INI->get() case change failed!');
    }

    public function testGetWorksWithDotNotation(): void
    {
        // Test dot notation.
        $this->assertEquals('localhost', self::$configuration->get('database.host'), 'Conf\INI->get() dot notation failed!');
    }

    public function testThatSpacesWhereConvertedToUnderScores(): void
    {
        // Test spaces to underscore.
        $this->assertEquals('space pants', self::$configuration->get('space_pants.look_at_my'), 'Conf\INI did not change spaces in keys to underscores!');
    }

    public function testThatPeriodsWhereConvertedToUnderScores(): void
    {
        // Test periods to underscore.
        $this->assertEquals('And that is a fact!', self::$configuration->get('other_stuff._i_like_dots_period'), 'Conf\INI) did not change periods in keys to underscores!');
    }

    public function testGetCanReturnArray(): void
    {
        // Test getting just an array from key.
        $sArr = array('LOOK_AT_MY' => 'space pants');
        $this->assertTrue(is_array(self::$configuration->get('space_pants')), 'Conf\INI->get() did not return an array for a "section"!');
        $this->assertEquals($sArr, self::$configuration->get('space_pants'), 'Conf\INI->get() did not return expected array for a "section"!');
    }

    public function testThatItCanMerge(): void
    {
        // Test the merging.
        $this->assertEquals('something', self::$configuration->get('devstuff.x'), 'Conf\INI did not properly merge values!');
        $this->assertEquals('ellisgl', self::$configuration->get('database.user'), 'Conf\INI did not properly merge values!');
    }

    public function testThatItCanReplaceSelfReferencedPlaceholders(): void
    {
        // Test the self referenced placeholder replacement.
        $this->assertEquals('mysql:host=localhost;dbname=ellisgldb', self::$configuration->get('database.dsn'), 'Conf\INI did not replace self referenced placeholders!');
    }

    public function testThatItDoesntReplaceMissingSelfReferencedPlaceholders(): void
    {
        // Make sure we do not replace things we can't reference.
        $this->assertEquals('@[doesnotexist]', self::$configuration->get('somestuff.d'), 'Conf\INI replaced a non existing self reference.');
    }

    public function testThatItCanReplaceRecursiveSelfReferencedPlaceholders(): void
    {
        // Test the recursive self referenced placeholder replacement.
        $this->assertEquals('We Can Do That!', self::$configuration->get('selfreferencedplaceholder.a'), 'Conf\INI did not replace the recursive self referenced placeholder!');
        $this->assertEquals('And this!', self::$configuration->get('selfreferencedplaceholder.b'), 'Conf\INI did not replace the recursive self referenced placeholder!');
        $this->assertEquals('This too!', self::$configuration->get('selfreferencedplaceholder.c'), 'Conf\INI did not replace the recursive self referenced placeholder!');
    }

    public function testThatItDoesntReplaceMissingRecursiveSelfReferencedPlaceholders(): void
    {
        // Make sure we do not replace things we can't reference.
        $this->assertEquals('@[doesnt].@[exist]', self::$configuration->get('somestuff.e'), 'Conf\INI replaced a non existing recursive self reference.');
        $this->assertEquals('@[doesnt.@[exist]]', self::$configuration->get('somestuff.f'), 'Conf\INI replaced a non existing recursive self reference.');
        $this->assertEquals('@[@[doesnt].exist]', self::$configuration->get('somestuff.g'), 'Conf\INI replaced a non existing recursive self reference.');
        $this->assertEquals('@[@[doesnt].@[exist]]', self::$configuration->get('somestuff.h'), 'Conf\INI replaced a non existing recursive self reference.');
    }

    public function testThatItCanReplaceEnvironmentVariablePlaceholders(): void
    {
        // Test environment variable replacement.
        $this->assertEquals('utf8', self::$configuration->get('database.charset'), 'Conf\INI did not replace an environment variable placeholder.');
    }

    public function testThatItCanReplaceEnvironmentVariablePlaceholderWithReferencedPlaceholders(): void
    {
        // Check to see if we can replace a environment variable placeholder that uses a self referenced placeholder inside of it.
        $this->assertEquals('utf8', self::$configuration->get('somestuff.k'), 'Conf\INI did not replace an environment variable placeholder with a self referenced placeholder inside it.');
    }

    public function testThatItDoesntReplaceMissingEnvironmentVariablePlaceholders(): void
    {
        $this->assertEquals('$[DOESNOTEXIST]', self::$configuration->get('somestuff.i'), 'Conf\INI replaced a non existing recursive self reference.');
    }
}
