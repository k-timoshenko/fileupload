<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\formatter\adapter;

use Imagine\Image\ImagineInterface;
use tkanstantsin\fileupload\formatter\ImagineFactory;
use tkanstantsin\fileupload\model\BaseObject;

abstract class AbstractImageAdapter extends BaseObject implements IFormatAdapter
{
    /**
     * @see ImagineFactory::get()
     * @var string
     */
    public $driver = ImagineFactory::DEFAULT_DRIVER;

    /**
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * @inheritdoc
     * @throws \Imagine\Exception\RuntimeException
     * @throws \tkanstantsin\fileupload\config\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->imagine = ImagineFactory::get($this->driver);
    }

}