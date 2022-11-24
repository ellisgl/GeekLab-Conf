<?php

namespace GeekLab\Conf\Driver;

use JsonException;

final class JSONConfDriver implements ConfDriverInterface
{
    private string $mainConfFile; // Path and file name of the top configuration file.
    private string $confLocation; // Path of the configuration files.

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
     * @param string | null $file If null, then load the main configuration file
     *
     * @return array
     * @throws JsonException
     */
    public function parseConfigurationFile(?string $file = null): array
    {
        $fileName = $file === null ? $this->mainConfFile : $this->confLocation . $file . '.json';
        $fileContents = file_get_contents($fileName);

        return !empty($fileContents) ? json_decode($fileContents, true, 512, JSON_THROW_ON_ERROR) : [];
    }
}
