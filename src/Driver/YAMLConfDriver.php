<?php

namespace GeekLab\Conf\Driver;
use  \yaml_parse_file;

final class YAMLConfDriver implements ConfDriverInterface
{
    /** @var string $mainConfigurationFile Path and file name of the top configuration file. */
    private $mainConfigurationFile;

    /** @var string $configurationLocation Path of the configuration files. */
    private $configurationLocation;

    /**
     * YAMLConfDriver constructor.
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
            return \yaml_parse_file($this->mainConfigurationFile);
        }

        return \yaml_parse_file($this->configurationLocation . $file . '.yaml');
    }
}
