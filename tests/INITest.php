<?php

use GeekLab\Conf;
use PHPUnit\Framework\TestCase;

/**
 * @covers \GeekLab\Conf\INI
 */
class INITest extends TestCase
{
    /** @var Conf\INI $configuration */
    private $configuration;

    /**
     * Set this up once for all the tests in side this.
     *
     * @covers \GeekLab\Conf\INI::init
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        // Load in a the main INI configuration.
        // Where the configurations are.
        $configurationDirectory = __DIR__ . '/data/ini/';

        // Main INI file.
        $systemFile = $configurationDirectory . 'system.ini';

        // Let's get loaded.
        $this->configuration = new Conf\INI($systemFile, $configurationDirectory);
        $this->configuration->init();

        // Sometimes you just want to see it.
        //var_dump($this->configuration->getAll());
    }

    public function testThatItIsAnObject(): void
    {
        $this->assertTrue(is_object($this->configuration), 'INI is not an object!');
    }

    public function testThatItImplementsConfigurationInterface(): void
    {
        $this->assertInstanceOf('GeekLab\Conf\ConfInterface', $this->configuration, 'Conf\INI does not implement Conf\ConfnInterface!');
    }

    public function testThatItExtendsConfigurationAbstract(): void
    {
        $this->assertInstanceOf('GeekLab\Conf\ConfAbstract', $this->configuration, 'Conf\INI does not an instance of Conf\ConfAbstract!');
    }

    /**
     * @covers \GeekLab\Conf\ConfAbstract::getAll
     */
    public function testGetAllReturnsArray(): void
    {
        // Get compiled config. Is it an array?
        $this->assertTrue(is_array($this->configuration->getAll()), 'Conf\INI->getAll() did not return an array!');
    }

    /**
     * @covers \GeekLab\Conf\ConfAbstract::get
     */
    public function testGetBasic(): void
    {
        // Basic get.
        $this->assertEquals('CrazyWebApp', $this->configuration->get('SERVICE'), 'Conf\INI->get() "Basic" failed!');
    }

    /**
     * @covers \GeekLab\Conf\ConfAbstract::get
     */
    public function testGetIsCaseInsensitive(): void
    {
        // Make sure it case doesn't matter.
        $this->assertEquals('CrazyWebApp', $this->configuration->get('SeRvIcE'), 'Conf\INI->get() case change failed!');
    }

    /**
     * @covers \GeekLab\Conf\ConfAbstract::get
     */
    public function testGetWorksWithDotNotation(): void
    {
        // Test dot notation.
        $this->assertEquals('localhost', $this->configuration->get('database.host'), 'Conf\INI->get() dot notation failed!');
    }

    /**
     * @covers \GeekLab\Conf\ConfAbstract::get
     */
    public function testThatSpacesWhereConvertedToUnderScores(): void
    {
        // Test spaces to underscore.
        $this->assertEquals('space pants', $this->configuration->get('space_pants.look_at_my'), 'Conf\INI did not change spaces in keys to underscores!');
    }

    /**
     * @covers \GeekLab\Conf\ConfAbstract::get
     */
    public function testThatPeriodsWhereConvertedToUnderScores(): void
    {
        // Test periods to underscore.
        $this->assertEquals('And that is a fact!', $this->configuration->get('other_stuff._i_like_dots_period'), 'Conf\INI) did not change periods in keys to underscores!');
    }

    /**
     * @covers \GeekLab\Conf\ConfAbstract::get
     */
    public function testGetCanReturnArray(): void
    {
        // Test getting just an array from key.
        $sArr = array('LOOK_AT_MY' => 'space pants');
        $this->assertTrue(is_array($this->configuration->get('space_pants')), 'Conf\INI->get() did not return an array for a "section"!');
        $this->assertEquals($sArr, $this->configuration->get('space_pants'), 'Conf\INI->get() did not return expected array for a "section"!');
    }

    /**
     * @covers \GeekLab\Conf\ConfAbstract::get
     */
    public function testThatItCanMerge(): void
    {
        // Test the merging.
        $this->assertEquals('something', $this->configuration->get('devstuff.x'), 'Conf\INI did not properly merge values!');
        $this->assertEquals('ellisgl', $this->configuration->get('database.user'), 'Conf\INI did not properly merge values!');
    }

    /**
     * @covers \GeekLab\Conf\ConfAbstract::get
     */
    public function testThatItCanReplaceSelfReferencedPlaceholders(): void
    {
        // Test the self referenced placeholder replacement.
        $this->assertEquals('mysql:host=localhost;dbname=ellisgldb', $this->configuration->get('database.dsn'), 'Conf\INI did not replace self referenced placeholders!');
    }

    /**
     * @covers \GeekLab\Conf\ConfAbstract::get
     */
    public function testThatItDoesntReplaceMissingSelfReferencedPlaceholders(): void
    {
        // Make sure we do not replace things we can't reference.
        $this->assertEquals('@[doesnotexist]', $this->configuration->get('somestuff.d'), 'Conf\INI replaced a non existing self reference.');
    }

    /**
     * @covers \GeekLab\Conf\ConfAbstract::get
     */
    public function testThatItCanReplaceRecursiveSelfReferencedPlaceholders(): void
    {
        // Test the recursive self referenced placeholder replacement.
        $this->assertEquals('We Can Do That!', $this->configuration->get('selfreferencedplaceholder.a'), 'Conf\INI did not replace the recursive self referenced placeholder!');
        $this->assertEquals('And this!', $this->configuration->get('selfreferencedplaceholder.b'), 'Conf\INI did not replace the recursive self referenced placeholder!');
        $this->assertEquals('This too!', $this->configuration->get('selfreferencedplaceholder.c'), 'Conf\INI did not replace the recursive self referenced placeholder!');
    }

    /**
     * @covers \GeekLab\Conf\ConfAbstract::get
     */
    public function testThatItDoesntReplaceMissingRecursiveSelfReferencedPlaceholders(): void
    {
        // Make sure we do not replace things we can't reference.
        $this->assertEquals('@[doesnt].@[exist]', $this->configuration->get('somestuff.e'), 'Conf\INI replaced a non existing recursive self reference.');
        $this->assertEquals('@[doesnt.@[exist]]', $this->configuration->get('somestuff.f'), 'Conf\INI replaced a non existing recursive self reference.');
        $this->assertEquals('@[@[doesnt].exist]', $this->configuration->get('somestuff.g'), 'Conf\INI replaced a non existing recursive self reference.');
        $this->assertEquals('@[@[doesnt].@[exist]]', $this->configuration->get('somestuff.h'), 'Conf\INI replaced a non existing recursive self reference.');
    }

    /**
     * @covers \GeekLab\Conf\ConfAbstract::get
     */
    public function testThatItCanReplaceEnvironmentVariablePlaceholders(): void
    {
        // Test environment variable replacement.
        $this->assertEquals('utf8', $this->configuration->get('database.charset'), 'Conf\INI did not replace an environment variable placeholder.');
    }

    /**
     * @covers \GeekLab\Conf\ConfAbstract::get
     */
    public function testThatItCanReplaceEnvironmentVariablePlaceholderWithReferencedPlaceholders(): void
    {
        // Check to see if we can replace a environment variable placeholder that uses a self referenced placeholder inside of it.
        $this->assertEquals('utf8', $this->configuration->get('somestuff.k'), 'Conf\INI did not replace an environment variable placeholder with a self referenced placeholder inside it.');
    }

    /**
     * @covers \GeekLab\Conf\ConfAbstract::get
     */
    public function testThatItDoesntReplaceMissingEnvironmentVariablePlaceholders(): void
    {
        $this->assertEquals('$[DOESNOTEXIST]', $this->configuration->get('somestuff.i'), 'Conf\INI replaced a non existing recursive self reference.');
    }
}
