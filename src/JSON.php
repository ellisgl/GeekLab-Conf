<?php

namespace GeekLab\Conf;

final class JSON extends ConfAbstract
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
                return  json_decode(file_get_contents($this->mainFile), true);
            },
            function ($file) use ($self) {
                // Load in the inner configurations and return an array.
                return  json_decode(file_get_contents($file . '.json'), true);
            }
        );
    }
}
