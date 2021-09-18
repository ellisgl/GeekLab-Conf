<?php

namespace GeekLab\Conf\Driver;

use Symfony\Component\Yaml\Yaml;

final class YAMLConfDriver implements ConfDriverInterface
{
    /** @var string $mainConfFile Path and file name of the top configuration file. */
    private string $mainConfFile;

    /** @var string $confLocation Path of the configuration files. */
    private string $confLocation;

    /**
     * YAMLConfDriver constructor.
     *
     * @param string $mainConfFile
     * @param string $confLocation
     */
    public function __construct(string $mainConfFile, string $confLocation)
    {
        $this->mainConfFile = $mainConfFile;
        $this->confLocation = $confLocation;
    }

    /**
     * Load and parse a configuration file and return an array.
     *
     * @param string | null $file If null, then load the main configuration file
     *
     * @return array
     */
    public function parseConfigurationFile(?string $file = null): array
    {
        if ($file === null) {
            return Yaml::parseFile($this->mainConfFile);
        }

        return Yaml::parseFile($this->confLocation . $file . '.yaml');
    }
}
