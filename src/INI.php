<?php

namespace GeekLab\Configuration;

final class INI extends ConfigurationAbstract
{
    private function import($iniFile)
    {
        // Load and parse the INI.
        $configuration = parse_ini_file($iniFile, true);

        // Uppercase and change spaces and periods to underscores in key names.
        $configuration = $this->conformArray($configuration);

        // Strip out anything that wasn't in a section
        // We don't want the ability to overwrite stuff from main INI file.
        foreach ($configuration as $key => $value) {
            if (!is_array($value)) {
                unset($configuration[$key]);
            }
        }

        // Combine/Merge/Overwrite new configuration with current.
        $this->configuration = array_replace_recursive($this->configuration, $configuration);
    }

    public function load(): void
    {
        // Load and parse the main INI file.
        $this->configuration = parse_ini_file($this->mainFile);

        // Conform the array: uppercase and changes spaces to underscores in keys.
        $this->configuration = $this->conformArray($this->configuration);

        // Load in the extra configuration via the CONF property.
        if (isset($this->configuration['CONF']) && is_array($this->configuration['CONF'])) {
            foreach ($this->configuration['CONF'] as $file) {
                $this->import($this->configurationLocation . $file . '.ini');
            }
        }

        // Fill in the placeholders.
        $this->configuration = $this->replacePlaceholders($this->configuration);
    }
}
