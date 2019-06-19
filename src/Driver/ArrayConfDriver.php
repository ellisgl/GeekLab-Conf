<?php

namespace GeekLab\Conf\Driver;

final class ArrayConfDriver implements ConfDriverInterface
{
    /** @var string $mainConfigurationFile Path and file name of the top configuration file. */
    private $mainConfigurationFile;

    /** @var string $configurationLocation Path of the configuration files. */
    private $configurationLocation;

    /**
     * ArrConfDriver constructor.
     *
     * @param string $mainConfFile
     * @param string $confLocation
     */
    public function __construct(string $mainConfFile, string $confLocation)
    {
        $this->mainConfigurationFile = $mainConfFile;
        $this->configurationLocation = $confLocation;
    }

    /**
     * Load and parse a configuration file and return an array.
     *
     * @param string|null $file If null, then load the main configuration file
     *
     * @return array
     */
    public function parseConfigurationFile(?string $file = null): array
    {
        if ($file === null) {
            return include $this->mainConfigurationFile;
        }

        return include $this->configurationLocation . $file . '.php';
    }
}
