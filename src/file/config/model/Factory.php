<?php

namespace tkanstantsin\fileupload\config\model;

/**
 * Class Factory
 */
class Factory
{
    /**
     * @see Alias
     * @var array
     */
    public $defaultConfig;

    /**
     * Factory constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->defaultConfig = $config;
        $this->defaultConfig['multiple'] = ($this->defaultConfig['max_count'] ?? 0) > 1;
    }

    /**
     * @param string|int $name
     * @param array|string $config
     * @return Alias
     * @throws \ErrorException
     */
    public function build(string $name, $config): Alias
    {
        if (!\is_string($config) && !\is_array($config)) {
            throw new \ErrorException('Invalid alias config for fileupload.');
        }
        if (\is_string($config)) { // using default config
            $config = [
                'class' => $config,
            ];
        }

        // add default options
        $config = array_replace($this->defaultConfig, $config);
        $config = array_merge($config, [
            'alias' => $name,
            'directory' => $config['directory'] ?? $name,
            'multiple' => $config['maxCount'] > 1,
        ]);

        if (!\in_array($config['hashMethod'], hash_algos(), true)) {
            throw new \ErrorException(sprintf('Hash method `%s` not found.', $config['hashMethod']));
        }

        return new Alias($config);
    }
}