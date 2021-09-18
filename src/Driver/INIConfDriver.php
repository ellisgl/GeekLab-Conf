<?php

namespace GeekLab\Conf\Driver;

final class INIConfDriver implements ConfDriverInterface
{
    /** @var string $mainConfFile Path and file name of the top configuration file. */
    private string $mainConfFile;

    /** @var string $confLocation Path of the configuration files. */
    private string $confLocation;

    /**
     * INIConfDriver constructor.
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
     * @return array[]
     */
    public function parseConfigurationFile(?string $file = null): array
    {
        $fileName = $file === null ? $this->mainConfFile : $this->confLocation . $file . '.ini';
        $parsed = parse_ini_file($fileName, $file !== null);

        return !empty($parsed) ? $parsed : [];
    }
}
