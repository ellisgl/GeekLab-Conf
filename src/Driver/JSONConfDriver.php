<?php

namespace GeekLab\Conf\Driver;

final class JSONConfDriver implements ConfDriverInterface
{
    /** @var string $mainConfFile Path and file name of the top configuration file. */
    private $mainConfFile;

    /** @var string $confLocation Path of the configuration files. */
    private $confLocation;

    /**
     * JSONConfDriver constructor.
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
     * @param string|null $file If null, then load the main configuration file
     *
     * @return array
     */
    public function parseConfigurationFile(?string $file = null): array
    {
        $fileName = $file === null ? $this->mainConfFile : $this->confLocation . $file . '.json';
        $fileContents = file_get_contents($fileName);

        return !empty($fileContents) ? json_decode($fileContents, true) : [];
    }
}
