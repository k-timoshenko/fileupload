<?php

namespace tkanstantsin\fileupload\model;

use tkanstantsin\fileupload\config\InvalidConfigException;

/**
 * Class BaseObject
 */
class BaseObject implements IConfigurable
{
    /**
     * Util constructor.
     * @param array $config
     * @throws InvalidConfigException
     */
    public function __construct(array $config = [])
    {
        Container::configure($this, $config);
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