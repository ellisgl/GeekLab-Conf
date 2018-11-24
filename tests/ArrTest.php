<?php

use GeekLab\Conf;
use PHPUnit\Framework\TestCase;

class ConfArr extends TestCase
{
    /**
     * @var Conf\Arr $configuration
     */
    protected static $configuration;

    // Set this up once for all the tests in side this.
    public static function setUpBeforeClass()
    {
        // Load in a the main Array configuration.
        // Where the configurations are.
        $configurationDirectory = __DIR__ . '/data/array/';

        // Main INI file.
        $systemFile = $configurationDirectory . 'system.php';

        // Let's get loaded.
        self::$configuration = new Conf\Arr($systemFile, $configurationDirectory);
        self::$configuration->init();

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
        $this->assertInstanceOf('GeekLab\Conf\ConfInterface', self::$configuration, 'Conf\Arr does not implement Conf\ConfnInterface!');
    }

    /** @test */
    public function testThatItExtendsConfigurationAbstract()
    {
        $this->assertInstanceOf('GeekLab\Conf\ConfAbstract', self::$configuration, 'Conf\Arr does not an instance of Conf\ConfAbstract!');
    }

    /** @test */
    public function testGetAllReturnsArray()
    {
        // Get compiled config. Is it an array?
        $this->assertTrue(is_array(self::$configuration->getAll()), 'Conf\Arr->getAll() did not return an array!');
    }

    /** @test */
    public function testGetBasic()
    {
        // Basic get.
        $this->assertEquals('CrazyWebApp', self::$configuration->get('SERVICE'), 'Conf\Arr->get() "Basic" failed!');
    }

    /** @test */
    public function testGetIsCaseInsensitive()
    {
        // Make sure it case doesn't matter.
        $this->assertEquals('CrazyWebApp', self::$configuration->get('SeRvIcE'), 'Conf\Arr->get() case change failed!');
    }

    /** @test */
    public function testGetWorksWithDotNotation()
    {
        // Test dot notation.
        $this->assertEquals('localhost', self::$configuration->get('database.host'), 'Conf\Arr->get() dot notation failed!');
    }

    /** @test */
    public function testThatSpacesWhereConvertedToUnderScores()
    {
        // Test spaces to underscore.
        $this->assertEquals('space pants', self::$configuration->get('space_pants.look_at_my'), 'Conf\Arr did not change spaces in keys to underscores!');
    }

    /** @test */
    public function testThatPeriodsWhereConvertedToUnderScores()
    {
        // Test periods to underscore.
        $this->assertEquals('And that is a fact!', self::$configuration->get('other_stuff._i_like_dots_period'), 'Conf\Arr) did not change periods in keys to underscores!');
    }

    /** @test */
    public function testGetCanReturnArray()
    {
        // Test getting just an array from key.
        $sArr = array('LOOK_AT_MY' => 'space pants');
        $this->assertTrue(is_array(self::$configuration->get('space_pants')), 'Conf\Arr->get() did not return an array for a "section"!');
        $this->assertEquals($sArr, self::$configuration->get('space_pants'), 'Conf\Arr->get() did not return expected array for a "section"!');
    }

    /** @test */
    public function testThatItCanMerge()
    {
        // Test the merging.
        $this->assertEquals('something', self::$configuration->get('devstuff.x'), 'Conf\Arr did not properly merge values!');
        $this->assertEquals('ellisgl', self::$configuration->get('database.user'), 'Conf\Arr did not properly merge values!');
    }

    /** @test */
    public function testThatItCanReplaceSelfReferencedPlaceholders()
    {
        // Test the self referenced placeholder replacement.
        $this->assertEquals('mysql:host=localhost;dbname=ellisgldb', self::$configuration->get('database.dsn'), 'Conf\Arr did not replace self referenced placeholders!');
    }

    /** @test */
    public function testThatItDoesntReplaceMissingSelfReferencedPlaceholders()
    {
        // Make sure we do not replace things we can't reference.
        $this->assertEquals('@[doesnotexist]', self::$configuration->get('somestuff.d'), 'Conf\Arr replaced a non existing self reference.');
    }

    /** @test */
    public function testThatItCanReplaceRecursiveSelfReferencedPlaceholders()
    {
        // Test the recursive self referenced placeholder replacement.
        $this->assertEquals('We Can Do That!', self::$configuration->get('selfreferencedplaceholder.a'), 'Conf\Arr did not replace the recursive self referenced placeholder!');
        $this->assertEquals('And this!', self::$configuration->get('selfreferencedplaceholder.b'), 'Conf\Arr did not replace the recursive self referenced placeholder!');
        $this->assertEquals('This too!', self::$configuration->get('selfreferencedplaceholder.c'), 'Conf\Arr did not replace the recursive self referenced placeholder!');
    }

    /** @test */
    public function testThatItDoesntReplaceMissingRecursiveSelfReferencedPlaceholders()
    {
        // Make sure we do not replace things we can't reference.
        $this->assertEquals('@[doesnt].@[exist]', self::$configuration->get('somestuff.e'), 'Conf\Arr replaced a non existing recursive self reference.');
        $this->assertEquals('@[doesnt.@[exist]]', self::$configuration->get('somestuff.f'), 'Conf\Arr replaced a non existing recursive self reference.');
        $this->assertEquals('@[@[doesnt].exist]', self::$configuration->get('somestuff.g'), 'Conf\Arr replaced a non existing recursive self reference.');
        $this->assertEquals('@[@[doesnt].@[exist]]', self::$configuration->get('somestuff.h'), 'Conf\Arr replaced a non existing recursive self reference.');
    }

    /** @test */
    public function testThatItCanReplaceEnvironmentVariablePlaceholders()
    {
        // Test environment variable replacement.
        $this->assertEquals('utf8', self::$configuration->get('database.charset'), 'Conf\Arr did not replace an environment variable placeholder.');
    }

    /** @test */
    public function testThatItCanReplaceEnvironmentVariablePlaceholderWithReferencedPlaceholders()
    {
        // Check to see if we can replace a environment variable placeholder that uses a self referenced placeholder inside of it.
        $this->assertEquals('utf8', self::$configuration->get('somestuff.k'), 'Conf\Arr did not replace an environment variable placeholder with a self referenced placeholder inside it.');
    }

    /** @test */
    public function testThatItDoesntReplaceMissingEnvironmentVariablePlaceholders()
    {
        $this->assertEquals('$[DOESNOTEXIST]', self::$configuration->get('somestuff.i'), 'Conf\Arr replaced a non existing recursive self reference.');
    }
}