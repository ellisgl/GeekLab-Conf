<?php

namespace GeekLab\Conf\Driver;

interface ConfDriverInterface
{
    /**
     * ConfDriverInterface constructor.
     *
     * @param string $mainConfFile Path and file name of the top configuration file.
     * @param string $confLocation Path of the rest of configuration files.
     */
    public function __construct(string $mainConfFile, string $confLocation);

    /**
     * Load and parse a configuration file and return an array.
     *
     * @param string | null $file If null, then load the main configuration file
     *
     * @return array[]
     */
    public function parseConfigurationFile(?string $file = null): array;
}
