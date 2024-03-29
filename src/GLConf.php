<?php

namespace GeekLab\Conf;

use GeekLab\Conf\Driver\ConfDriverInterface;

final class GLConf
{
    protected array $configuration = [];
    private ConfDriverInterface $driver;
    private array $injectedValues;
    private array $options;

    /**
     * GLConf constructor.
     * Inject our driver (strategy) here.
     *
     * @param ConfDriverInterface $driver
     * @param array               $valueInjections
     * @param array               $options
     */
    public function __construct(
        ConfDriverInterface $driver,
        array $valueInjections = [],
        array $options = ['keys_upper_case']
    ) {
        $this->driver = $driver;
        $this->injectedValues = $valueInjections;
        $this->options = $options;
    }

    /**
     * Get data by dot notation.
     * Stolen from: https://stackoverflow.com/a/14706302/344028
     *
     * @param string $key dot notated array key accessor.
     *
     * @return mixed
     */
    public function get(string $key): mixed
    {
        if (in_array('keys_lower_case', $this->options, true)) {
            // Convert key to lower case.
            $key = strtolower($key);
        } elseif (!in_array('keys_same_case', $this->options, true)) {
            // Convert key to upper case.
            $key = strtoupper($key);
        }

        /** @var mixed $config Save configuration for local scope modification. */
        $config = $this->configuration;

        /** @var array<string> | bool $token Tokenize the key to do iterations over the config with. */
        $token = strtok($key, '.');

        // Loop until we are out of tokens.
        while ($token !== false) {
            if (!isset($config[$token])) {
                // Array key of $token wasn't found.
                return false;
            }

            // Save the data found.
            $config = $config[$token];

            // Advanced to the next token, or set token to false if nothing else if left..
            $token = strtok('.');
        }

        // Return the valid found by the previous loop.
        return $config;
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
     * Initialize the configuration system.
     */
    public function init(): void
    {
        $confKey = 'conf';
        if (in_array('keys_upper_case', $this->options, true)) {
            $confKey = 'CONF';
        }

        // Load main (top level) configuration.
        $this->configuration = $this->driver->parseConfigurationFile();

        // Overload configuration with injected values.
        $this->configuration = array_merge($this->configuration, $this->injectedValues);

        // Conform the array (cases and changes spaces to underscore characters in keys).
        $this->configuration = $this->conformArray($this->configuration);
        $config = [];

        // Load in the extra configuration via the CONF property.
        if (isset($this->configuration[$confKey]) && is_array($this->configuration[$confKey])) {
            foreach ($this->configuration[$confKey] as $file) {
                // Load in the referenced configuration from the main configuration.
                $innerConfig = $this->driver->parseConfigurationFile($file);

                // Conform the configuration array.
                $innerConfig = $this->conformArray($innerConfig);

                // Strip out anything that wasn't in a section (non-array value at the top level).
                // We don't want the ability to overwrite stuff from main configuration file.
                foreach ($innerConfig as $k => $v) {
                    if (!is_array($v)) {
                        unset($innerConfig[$k]);
                    }
                }

                // Store conformed configuration into temporary array for merging later.
                $config[] = $innerConfig;
            }

            // Combine/Merge/Overwrite compiled configuration with current.
            // Uses the splat operator on the arrays stored in the temporary config.
            $this->configuration = array_replace_recursive($this->configuration, ...$config) ?: [];
        }

        // Fill in the placeholders.
        $this->configuration = $this->processConfig($this->configuration);
    }

    /**
     * Make the array conform to some sort of standard.
     * -  Convert key names cases (maybe).
     * -  Convert spaces and periods to underscores.
     *
     * @param array $arr
     *
     * @return array
     */
    protected function conformArray(array $arr): array
    {
        // Store our conformed array for returning.
        $fixed = [];

        if (in_array('keys_lower_case', $this->options, true)) {
            // Convert keys to lower case.
            $arr = array_change_key_case($arr);
        } elseif (!in_array('keys_same_case', $this->options, true)) {
            // Convert keys to upper case.
            $arr = array_change_key_case($arr, CASE_UPPER);
        }

        foreach ($arr as $k => $v) {
            // Recursively conform the inner arrays.
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
     * @param string $value
     *
     * @return string
     */
    private function fillPlaceHolders(string $value): string
    {
        // Certain recursive stuff, like @[SelfReferencedPlaceholder.@[SomeStuff.a]] is what triggers this part.
        // Find the self referenced placeholders and fill them.
        // Force the type to string, in possible case of null.
        $data = (string) preg_replace_callback(
            '/@\[([a-zA-Z0-9_.-]*?)]/',
            function ($matches): string {
                // Does this key exist, is so fill this match, if not, just return the match intact.
                return $this->get($matches[1]) ?: $matches[0];
            },
            $value
        );

        // Find the recursive self referenced placeholders and fill them.
        if ($data !== $value && preg_match('/@\[([a-zA-Z0-9_.-]*?)]/', $data)) {
            $data = (string) $this->processConfig($data);
        }

        // Find the environment variable placeholders and fill them.
        // Force the type to string, in possible case of null.
        return (string) preg_replace_callback(
            '/\$\[([a-zA-Z0-9_.-]*?)]/',
            static function ($matches) {
                // Replace with local variable (non-SAPI)
                // Or keep intact if one isn't found.
                return !empty(getenv($matches[1], true)) ? getenv($matches[1], true) : $matches[0];
            },
            $data
        );
    }

    /**
     * Run through the configuration and process the placeholders.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    private function processConfig(mixed $data): mixed
    {
        if (is_array($data)) {
            // It's an array, so let's loop through it.
            foreach ($data as $k => $val) {
                $data[$k] = is_string($val) ? $this->fillPlaceHolders($val) : $this->processConfig($val);
            }
        } elseif (is_string($data)) {
            $data = $this->fillPlaceHolders($data);
        }

        return $data;
    }
}
