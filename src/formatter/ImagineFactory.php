<?php

namespace tkanstantsin\fileupload\formatter;

use Imagine\Gd\Imagine as GdImagine;
use Imagine\Gmagick\Imagine as GmagickImagine;
use Imagine\Image\ImagineInterface;
use Imagine\Imagick\Imagine as ImagickImagine;
use tkanstantsin\fileupload\config\InvalidConfigException;

/**
 * Class ImageDriverFactory
 */
class ImagineFactory
{
    public const DEFAULT_DRIVER = self::IMAGICK;

    /* Available drivers */
    public const GD = 'gd';
    public const IMAGICK = 'imagick';
    public const GMAGICK = 'gmagick';

    /**
     * Create imagine object for given driver
     * @param string $type
     * @return ImagineInterface
     * @throws \Imagine\Exception\RuntimeException
     * @throws InvalidConfigException
     */
    public static function get(string $type): ImagineInterface
    {
        switch ($type) {
            case self::GD:
                return new GdImagine();
            case self::IMAGICK:
                return new ImagickImagine();
            case self::GMAGICK:
                return new GmagickImagine();
            default:
                throw new InvalidConfigException(sprintf('Image driver %s not found.', $type));
        }
    }
}