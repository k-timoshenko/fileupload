<?php

namespace tkanstantsin\fileupload\model;

/**
 * Class BaseObject
 */
class BaseObject
{
    /**
     * Util constructor.
     * @param array $config
     * @throws \RuntimeException
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new \RuntimeException(sprintf('Property %s in class %s not found.', $key, static::class));
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