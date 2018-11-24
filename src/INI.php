<?php

namespace GeekLab\Conf;

final class INI extends ConfAbstract
{
    /**
     * Initialize the configuration system.
     */
    public function init(): void
    {
        // Make $this available to the callback.
        $self = &$this;

        // Load in the configurations.
        $this->load(
            function () {
                // Load in the main configuration file and return an array.
                return parse_ini_file($this->mainFile);
            },
            function ($file) use ($self) {
                // Load in the inner configurations and return an array.
                return parse_ini_file($file . '.ini', true);
            }
        );
    }
}
