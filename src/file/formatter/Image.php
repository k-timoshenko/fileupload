<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 10/16/16
 * Time: 2:54 PM
 */

namespace tkanstantsin\fileupload\formatter;

use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Imagick\Imagine;

/**
 * Class ImageProcessor
 */
class Image extends File
{
    /**
     * Like background `cover` in css.
     */
    public const RESIZE_OUTBOUND = \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
    /**
     * Like background `contain` in css.
     */
    public const RESIZE_INSET = \Imagine\Image\ImageInterface::THUMBNAIL_INSET;

    public const DEFAULT_EXTENSION = 'jpg';

    /**
     * @var int
     */
    public $width;
    /**
     * @var int
     */
    public $height;
    /**
     * @var string
     */
    public $mode = self::RESIZE_INSET;

    /**
     * @var Imagine
     */
    protected $imagine;

    /**
     * @inheritdoc
     * @throws \Imagine\Exception\InvalidArgumentException
     * @throws \Imagine\Exception\RuntimeException
     */
    protected function getContentInternal()
    {
        $this->imagine = new Imagine();
        $image = $this->imagine->read(parent::getContentInternal());
        $image = $this->format($image);

        return $image->get($this->file->getExtension() ?? self::DEFAULT_EXTENSION);
    }

    /**
     * @param ImageInterface $image
     * @return ImageInterface
     * @throws \Imagine\Exception\RuntimeException
     * @throws \Imagine\Exception\InvalidArgumentException
     */
    protected function format(ImageInterface $image): ImageInterface
    {
        if ($this->width === null || $this->height === null) {
            return $image;
        }

        $box = new Box($this->width, $this->height);

        return $image->thumbnail($box, $this->mode);
    }
}