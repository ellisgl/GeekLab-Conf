<?php

namespace GeekLab\Conf;

use GeekLab\Conf\Driver\ConfDriverInterface;

final class GLConf
{
    /** @var ConfDriverInterface $driver */
    private $driver;

    /** @var array $configuration The compiled configuration. */
    protected $configuration = [];

    public function __construct(ConfDriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Return the compiled configuration.
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->configuration;
    }

    /**
     * Get data by dot notation.
     * Stolen from: https://stackoverflow.com/a/14706302/344028
     *
     * @param string $key dot notated array key accessor.
     * @return mixed
     */
    public function get(string $key)
    {
        /**
         * @var string $key convert key to upper case.
         */
        $key = strtoupper($key);

        /**
         * @var mixed $conf Save conf to local scope.
         */
        $conf = $this->configuration;

        /**
         * @var bool|array $pos Tokenize the key to do iterations over the conf with.
         */
        $pos = strtok($key, '.');

        while ($pos !== false) {
            if (!isset($conf[$pos])) {
                return false;
            }

            $conf = $conf[$pos];
            $pos  = strtok('.');
        }

        return $conf;
    }

    /**
     * Make the array conform to some standards.
     *   Convert key names to uppercase.
     *   Convert spaces and periods to underscores.
     *
     * @param array $arr
     * @return array
     */
    protected function conformArray(array $arr): array
    {
        // Store our conformed array for returning.
        $fixed = array();

        // Convert keys to uppercase
        $arr = array_change_key_case($arr, CASE_UPPER);

        foreach ($arr as $k => $v) {
            // Recursively conform inner arrays.
            if (is_array($v)) {
                $v = $this->conformArray($v);
            }

            // Replace spaces and periods with underscores.
            $fixed[preg_replace('/\s+|\.+/', '_', $k)] = $v;
        }

        // Return the conformed array.
        return $fixed;
    }

    /**
     * Self referenced and environment variable placeholder replacement.
     *
     * @param  mixed $data
     * @return mixed
     */
    protected function replacePlaceholders($data)
    {
        if (is_array($data)) {
            // It's an array, so let's loop through it.
            foreach ($data as $k => $val) {
                if (!is_array($val)) {
                    // Find the self referenced placeholders and fill them.
                    $data[$k] = preg_replace_callback('/\@\[([a-zA-Z0-9_.-]*?)\]/', function ($matches) {
                        // Does this key exist, is so fill this match, if not, just return the match intact.
                        $ret = $this->get($matches[1]) ? $this->get($matches[1]) : $matches[0];

                        return $ret;
                    }, $val);

                    // Find the recursive self referenced placeholders and fill them.
                    if ($data[$k] !== $val && preg_match('/\@\[([a-zA-Z0-9_.-]*?)\]/', $data[$k])) {
                        $data[$k] = $this->replacePlaceholders($data[$k]);
                    }

                    // Find the environment variable placeholders and fill them.
                    $data[$k] = preg_replace_callback('/\$\[([a-zA-Z0-9_.-]*?)\]/', function ($matches) {
                        if (!empty(getenv($matches[1], true))) {
                            $ret = getenv($matches[1], true);
                        } elseif (!empty(getenv($matches[1]))) {
                            $ret = getenv($matches[1]);
                        } else {
                            $ret = $matches[0];
                        }

                        return $ret;
                    }, $data[$k]);
                } else {
                    // Go into the array.
                    $data[$k] = $this->replacePlaceholders($val);
                }
            }
        } elseif (is_string($data)) {
            // It's a string!
            // Certain recursive stuff, like @[SelfReferencedPlaceholder.@[SomeStuff.a]] is what triggers this part.
            // Find the self referenced placeholders and fill them.
            $data = preg_replace_callback('/\@\[([a-zA-Z0-9_.-]*?)\]/', function ($matches) {
                // Does this key exist, is so fill this match, if not, just return the match intact.
                $ret = ($this->get($matches[1])) ? $this->get($matches[1]) : $matches[0];

                // Looks like we have a recursive self referenced placeholder.
                if ($ret !== $matches[0] && preg_match('/\@\[(.*?)\]/', $matches[0])) {
                    $ret = $this->replacePlaceholders($ret);
                }

                return $ret;
            }, $data);
        }

        return $data;
    }


    /**
     * Init the configuration system.
     */
    public function init(): void
    {
        // Load main (top level) configuration and conform it (uppercase and changes spaces to underscores in keys.).
        $this->configuration = $this->driver->parseConfigurationFile();
        $this->configuration = $this->conformArray($this->configuration);
        $configuration = [];

        // Load in the extra configuration via the CONF property.
        if (isset($this->configuration['CONF']) && is_array($this->configuration['CONF'])) {
            foreach ($this->configuration['CONF'] as $file) {
                // Use the callback ($outerCallback) to load the configuration file.
                $innerConfiguration = $this->driver->parseConfigurationFile($file);

                // Uppercase and change spaces and periods to underscores in key names.
                $innerConfiguration = $this->conformArray($innerConfiguration);

                // Strip out anything that wasn't in a section
                // We don't want the ability to overwrite stuff from main configuration file.
                foreach ($innerConfiguration as $k => $v) {
                    if (!is_array($v)) {
                        unset($innerConfiguration[$k]);
                    }
                }

                $configuration[] = $innerConfiguration;
            }

            // Combine/Merge/Overwrite configuration with current.
            $this->configuration = array_replace_recursive($this->configuration, ...$configuration);
        }

        // Fill in the placeholders.
        $this->configuration = $this->replacePlaceholders($this->configuration);
    }
}
