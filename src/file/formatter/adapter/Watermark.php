<?php

namespace tkanstantsin\fileupload\formatter\adapter;

use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Imagine\Imagick\Imagine;
use tkanstantsin\fileupload\config\InvalidConfigException;
use tkanstantsin\fileupload\formatter\Image;
use tkanstantsin\fileupload\model\BaseObject;
use tkanstantsin\fileupload\model\IFile;

/**
 * Class Watermark
 */
class Watermark extends BaseObject implements IFormatAdapter
{
    public const POSITION_CENTER_CENTER = 'center_center';
    public const POSITION_CENTER_LEFT = 'center_left';
    public const POSITION_CENTER_RIGHT = 'center_right';
    public const POSITION_TOP_CENTER = 'top_center';
    public const POSITION_TOP_LEFT = 'top_left';
    public const POSITION_TOP_RIGHT = 'top_right';
    public const POSITION_BOTTOM_CENTER = 'bottom_center';
    public const POSITION_BOTTOM_LEFT = 'bottom_left';
    public const POSITION_BOTTOM_RIGHT = 'bottom_right';

    public const SIZE_COVER = 'cover';
    public const SIZE_CONTAIN = 'contain';
    public const SIZE_STRETCH = 'stretch';

    /**
     * Absolute path to image file
     * @var string|null
     */
    public $markFilepath;
    /**
     * Water mark image binary
     * @var string|resource|null
     */
    public $markContent;

    /**
     * Opacity for watermark image
     * @var int
     */
    public $opacity = 100;
    /**
     * @var string
     */
    public $position = self::POSITION_CENTER_CENTER;
    /**
     * @var string
     */
    public $size = self::SIZE_CONTAIN;

    /**
     * @inheritdoc
     * @throws \tkanstantsin\fileupload\config\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        if ($this->markFilepath === null && $this->markContent === null) {
            throw new InvalidConfigException('Either watermarkPath or watermarkImage must be defined.');
        }
    }

    /**
     * Applies filters or something to content and return it
     *
     * @param IFile $file
     * @param       $content
     *
     * @return mixed
     * @throws \Imagine\Exception\InvalidArgumentException
     * @throws \Imagine\Exception\RuntimeException
     * @throws \ImageOptimizer\Exception\Exception
     */
    public function exec(IFile $file, $content)
    {
        $imagine = new Imagine();

        $image = $imagine->read($content);
        $imageSize = $image->getSize();

        $watermark = $this->getWatermark($imagine);
        $watermarkSize = $watermark->getSize();

        // TODO: implement watermark adapter.

        /* @see http://urmaul.com/blog/imagick-filters-comparison */
        $watermark = $watermark->resize($imageSize, ImageInterface::FILTER_SINC);
        $point = new Point(0, 0);

        $image = $image->paste($watermark, $point);

        return $image->get($file->getExtension() ?? Image::DEFAULT_EXTENSION);
    }

    private function getWatermark(Imagine $imagine): ImageInterface
    {
        $resource = null;
        if ($this->markFilepath !== null) {
            $resource = fopen($this->markFilepath, 'rb');
        } elseif (\is_string($this->markContent)) {
            $resource = 'a'; // TODO: create resource.
        } elseif (\is_resource($this->markContent)) {
            $resource = $this->markContent;
        }

        if ($resource === null) {
            throw new \UnexpectedValueException('Water mark image invalid format');
        }

        return $imagine->read($resource);
    }
}