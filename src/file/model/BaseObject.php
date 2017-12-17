<?php

namespace tkanstantsin\fileupload\model;

use tkanstantsin\fileupload\config\InvalidConfigException;

/**
 * Class BaseObject
 */
class BaseObject
{
    /**
     * Util constructor.
     * @param array $config
     * @throws InvalidConfigException
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new InvalidConfigException(sprintf('Property %s in class %s not found.', $key, static::class));
            }
            $this->$key = $value;
        }

        $this->init();
    }

    /**
     * Initialize object
     * E.g. instantiate some variables or check correct data
     */
    public function init(): void
    {
    }
}