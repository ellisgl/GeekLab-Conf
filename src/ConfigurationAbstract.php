<?php

namespace GeekLab\Configuration;

abstract class ConfigurationAbstract implements ConfigurationInterface
{
    /**
     * @var string $mainFile Path/filename of the main configuration file.
     */
    protected $mainFile;

    /**
     * @var string $configurationLocation Path of the extra configuration files.
     */
    protected $configurationLocation;

    /**
     * @var array $configuration The generated configuration.
     */
    protected $configuration = array();

    /**
     * @param string $mainFile              Path/filename of the main configuration file.
     * @param string $configurationLocation Path of the extra configuration files.
     */
    public function __construct(string $mainFile, string $configurationLocation)
    {
        $this->mainFile              = $mainFile;
        $this->configurationLocation = $configurationLocation;
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
     * Get data with dot notation.
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
         * @var mixed $configuration Save configuration to local scope.
         */
        $configuration = $this->configuration;

        /**
         * @var bool|array $position Tokenize the key to do iterations over the configuration with.
         */
        $position = strtok($key, '.');
        while ($position !== false) {
            if (!isset($configuration[$position])) {
                return false;
            }

            $configuration = $configuration[$position];
            $position      = strtok('.');
        }

        return $configuration;
    }

    /**
     * Make the array conform to some standards.
     *   Convert key names to uppercase.
     *   Convert spaces and periods to underscores.
     *
     * @param array $array
     * @return array
     */
    protected function conformArray(array $array): array
    {
        // Store our conformed array for returning.
        $fixed = array();

        // Convert keys to uppercase
        $array = array_change_key_case($array, CASE_UPPER);

        foreach ($array as $k => $v) {
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
            foreach ($data as $key => $value) {
                if (!is_array($value)) {
                    // Find the self referenced placeholders and fill them.
                    $data[$key] = preg_replace_callback('/\@\[([a-zA-Z0-9_.-]*?)\]/', function ($matches) {
                        //var_dump($matches);
                        $ret = ($this->get($matches[1])) ? $this->get($matches[1]) : $matches[0];
                        return $ret;
                    }, $value);

                    // Find the recursive self referenced placeholders and fill them.
                    if ($data[$key] !== $value && preg_match('/\@\[([a-zA-Z0-9_.-]*?)\]/', $data[$key])) {
                        $data[$key] = $this->replacePlaceholders($data[$key]);
                    }

                    // Find the environment variable placeholders and fill them.
                    $data[$key] = preg_replace_callback('/\$\[([a-zA-Z0-9_.-]*?)\]/', function ($matches) {
                        if (!empty(getenv($matches[1], true))) {
                            $ret = getenv($matches[1], true);
                        } elseif (!empty(getenv($matches[1]))) {
                            $ret = getenv($matches[1]);
                        } else {
                            $ret = $matches[0];
                        }

                        return $ret;
                    }, $data[$key]);
                } else {
                    // Go into the array.
                    $data[$key] = $this->replacePlaceholders($value);
                }
            }
        } elseif (is_string($data)) {
            // It's a string, which means that it will get hit on certain recursive stuff, like @[selfreferencedplaceholder.@[somestuff.a]].
            // Find the self referenced placeholders and fill them.

            $data = preg_replace_callback('/\@\[([a-zA-Z0-9_.-]*?)\]/', function ($matches) {
                $ret = ($this->get($matches[1])) ? $this->get($matches[1]) : $matches[0];

                // Looks like we have a recursive self referenced placeholder.
                if ($ret !== $matches[0] && preg_match('/\@\[(.*?)\]/', $ret)) {
                    $ret = $this->replacePlaceholders($ret);
                }

                return $ret;
            }, $data);
        }

        return $data;
    }
}
