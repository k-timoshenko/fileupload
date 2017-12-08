<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 10/16/16
 * Time: 11:11 PM
 */

namespace tkanstantsin\fileupload\processor;

/**
 * Class ImageConfig
 */
class ImageConfig extends FileConfig
{
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
    public $mode = \Imagine\Image\ImageInterface::THUMBNAIL_INSET;
}