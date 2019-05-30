<?php

namespace GeekLab\Conf;

final class Arr extends ConfAbstract
{
     /**
     * Initialize the configuration system.
     */
    public function init(): void
    {
        // Load in the configurations.
        $this->load(
            function () {
                // Load in the main configuration file and return an array.
                return include($this->mainFile);
            },
            function ($file) {
                // Load in the inner configurations and return an array.
                return include($file . '.php');
            }
        );
    }
}
