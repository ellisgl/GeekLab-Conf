<?php

namespace GeekLab\Conf;

final class INI extends ConfAbstract
{
    /**
     * Import an individual file.
     *
     * @param string $file
     */
    private function import(string $file): void
    {
        // Load and parse the INI.
        $conf = parse_ini_file($file, true);

        // Uppercase and change spaces and periods to underscores in key names.
        $conf = $this->conformArray($conf);

        // Strip out anything that wasn't in a section
        // We don't want the ability to overwrite stuff from main INI file.
        foreach ($conf as $k => $v) {
            if (!is_array($v)) {
                unset($conf[$k]);
            }
        }

        // Combine/Merge/Overwrite new configuration with current.
        $this->conf = array_replace_recursive($this->conf, $conf);
    }

    /**
     * Load in the configuration files.
     */
    public function load(): void
    {
        // Load and parse the main INI file.
        $this->conf = parse_ini_file($this->mainFile);

        // Conform the array: uppercase and changes spaces to underscores in keys.
        $this->conf = $this->conformArray($this->conf);

        // Load in the extra configuration via the CONF property.
        if (isset($this->conf['CONF']) && is_array($this->conf['CONF'])) {
            foreach ($this->conf['CONF'] as $file) {
                $this->import($this->confLocation . $file . '.ini');
            }
        }

        // Fill in the placeholders.
        $this->conf = $this->replacePlaceholders($this->conf);
    }
}
