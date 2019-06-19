<?php

namespace GeekLab\Conf\Driver;

final class JSONConfDriver implements ConfDriverInterface
{
    /** @var string $mainConfigurationFile Path and file name of the top configuration file. */
    private $mainConfigurationFile;

    /** @var string $configurationLocation Path of the configuration files. */
    private $configurationLocation;

    /**
     * JSONConfDriver constructor.
     *
     * @param string $mainConfigurationFile
     * @param string $configurationLocation
     */
    public function __construct(string $mainConfigurationFile, string $configurationLocation)
    {
        $this->mainConfigurationFile = $mainConfigurationFile;
        $this->configurationLocation = $configurationLocation;
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
            $fileContents = file_get_contents($this->mainConfigurationFile);
        } else {
            $fileContents = file_get_contents($this->configurationLocation . $file . '.json');
        }

        return !empty($fileContents) ? json_decode($fileContents, true) : [];
    }
}
