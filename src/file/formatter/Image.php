<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 10/16/16
 * Time: 2:54 PM
 */

namespace tkanstantsin\fileupload\formatter;

use Imagine\Image\Box;
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
     * @inheritdoc
     * @throws \Imagine\Exception\InvalidArgumentException
     * @throws \Imagine\Exception\RuntimeException
     */
    public function getContentInternal()
    {
        $image = (new Imagine())->read(parent::getContentInternal());
        $box = $this->width !== null && $this->height !== null
            ? new Box($this->width, $this->height)
            : $image->getSize(); // means don't change image size
        $thumb = $image->thumbnail($box, $this->mode);

        return $thumb->get($this->file->getExtension() ?? self::DEFAULT_EXTENSION);
    }
}