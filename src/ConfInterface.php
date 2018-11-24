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
     * Initialize the configuration system.
     * Use callbacks to load the file and convert to array and
     */
    public function init(): void;
}
