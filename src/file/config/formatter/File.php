<?php

namespace tkanstantsin\fileupload\config\formatter;

/**
 * Class ImageConfig
 */
class File
{
    /**
     * Value of config constant, that was use to generate this config.
     * @var string
     */
    public $name;
    /**
     * @see \tkanstantsin\fileupload\model\Type
     * @var int
     */
    public $fileTypeId;

    /**
     * FileConfig constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }
}