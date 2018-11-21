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
        // let's get less descriptive.
        define('DS', DIRECTORY_SEPARATOR);

        // Load in a the main INI with just Webapp and Dev.
        // Main INI file.
        $systemFile = __DIR__ . DS . 'data' . DS . 'ini' . DS . 'system';

        // Where configuration INIs are.
        $configurationDirectory = __DIR__ . DS . 'data' . DS . 'ini' . DS . 'configurations' . DS;

        // Let's get loaded.
        self::$configuration = new GeekLab\Configuration\INI();
        self::$configuration->load();
    }

    /** @test */
    public function testThatItIsAnObject()
    {
        $this->assertTrue(is_object(self::$configuration), 'INI is not an object!');
    }

    /** @test */
    public function testThatItImplementsConfigurationInterface()
    {
        $class = new ReflectionClass('GeekLab\Configuration\INI');

        $this->assertTrue($class->implementsInterface('GeekLab\Configuration\ConfigurationInterface'), 'Configuration\INI does not implement Configuration\ConfigurationInterface!');
    }
}