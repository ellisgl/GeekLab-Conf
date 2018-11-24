<?php

namespace GeekLab\Conf;

interface ConfInterface
{
    /**
     * @param string $mainFile
     * @param string $confLocation
     */
    public function __construct(string $mainFile, string $confLocation);

    /**
     * Return the compiled array.
     *
     * @return array
     */
    public function getAll(): array;

    /**
     * Get data with dot notation.
     *
     * @param string $key dot notated array key accessor.
     * @return mixed
     */
    public function get(string $key);

    /**
     * Load should do the following:
     *   Load in main configuration.
     *   Conform that array.
     *   Load in the extra files listed in the main configuration file.
     *   Conform those.
     *   Replace the placeholders.
     */
    public function init(): void;
}
