<?php

namespace tkanstantsin\fileupload\config\formatter;

/**
 * Class ImageConfig
 *
 * @todo custom 404 image for each formatter.
 */
class Image extends File
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