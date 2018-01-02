<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\config;

/**
 * Class Factory
 */
class Factory
{
    /**
     * @see Alias
     * @var array
     */
    public $defaultAliasConfig;

    /**
     * @var Alias[]
     */
    protected $aliasArray = [];

    /**
     * Prepares aliases config in proper view.
     * @param array $defaultAliasConfig
     * @param array $aliasArray
     * @return array
     * @throws InvalidConfigException
     */
    public static function prepareAliases(array $defaultAliasConfig, array $aliasArray): array
    {
        $factory = new static($defaultAliasConfig);
        foreach ($aliasArray as $name => $config) {
            $name = (string) $name;
            $aliasArray[$name] = $factory->createAlias($name, $config);
        }

        return $aliasArray;
    }

    /**
     * @param array $defaultAliasConfig
     * @return Factory
     */
    public static function build(array $defaultAliasConfig): self
    {
        return new static($defaultAliasConfig);
    }

    /**
     * Factory constructor.
     * @param array $defaultAliasConfig
     */
    public function __construct(array $defaultAliasConfig)
    {
        $this->defaultAliasConfig = $defaultAliasConfig;
        $this->defaultAliasConfig['multiple'] = ($this->defaultAliasConfig['max_count'] ?? 0) > 1;
    }

    /**
     * Add config
     * @param string $name
     * @param $config
     * @throws InvalidConfigException
     */
    public function add(string $name, $config): void
    {
        $this->aliasArray[$name] = $this->createAlias($name, $config);
    }

    /**
     * @param array $configArray
     * @throws InvalidConfigException
     */
    public function addMultiple(array $configArray): void
    {
        foreach ($configArray as $name => $config) {
            $this->add((string) $name, $config);
        }
    }

    /**
     * @param string $name
     * @return Alias
     * @throws \RuntimeException
     */
    public function getAliasConfig(string $name): Alias
    {
        $aliasConfig = $this->aliasArray[$name] ?? null;
        if ($aliasConfig === null) {
            throw new \RuntimeException(sprintf('Alias with key `%s` not defined.', $name));
        }

        return $aliasConfig;
    }

    /**
     * @param string|int $name
     * @param array|string $config
     * @return Alias
     * @throws InvalidConfigException
     */
    protected function createAlias(string $name, $config): Alias
    {
        if (!\is_string($config) && !\is_array($config)) {
            throw new InvalidConfigException('Invalid alias config for fileupload.');
        }
        if (\is_string($config)) { // using default config
            $config = [
                'class' => $config,
            ];
        }

        // add default options
        $config = array_replace($this->defaultAliasConfig, $config);
        $config = array_merge($config, [
            'alias' => $name,
            'directory' => $config['directory'] ?? $name,
            'multiple' => $config['maxCount'] > 1,
        ]);

        if (!\in_array($config['hashMethod'], hash_algos(), true)) {
            throw new InvalidConfigException(sprintf('Hash method `%s` not found.', $config['hashMethod']));
        }

        return new Alias($config);
    }
}