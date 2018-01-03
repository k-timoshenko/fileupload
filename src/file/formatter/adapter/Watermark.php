<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\formatter\adapter;

use Imagine\Image\BoxInterface;
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
     * TODO: implement position.
     * @var string
     */
    public $position = self::POSITION_CENTER_CENTER;
    /**
     * TODO: implement size.
     * @var string
     */
    public $size = self::SIZE_CONTAIN;
    /**
     * Watermark size relative to image size
     * @var float
     */
    public $scale = 0.9;

    /**
     * @inheritdoc
     * @throws \tkanstantsin\fileupload\config\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        if ($this->scale > 1 || $this->scale <= 0) {
            throw new InvalidConfigException('Scale must be greater than 0 and lower or equal than 1.');
        }
        if ($this->markFilepath === null && $this->markContent === null) {
            throw new InvalidConfigException('Either watermarkPath or watermarkImage must be defined.');
        }
        if ($this->markFilepath !== null && !file_exists($this->markFilepath)) {
            throw new InvalidConfigException(sprintf('Watermark by path `%s` not found.', $this->markFilepath));
        }
    }

    /**
     * Applies filters or something to content and return it
     *
     * @param IFile $file
     * @param       $content
     *
     * @return mixed
     * @throws \UnexpectedValueException
     * @throws \Imagine\Exception\InvalidArgumentException
     * @throws \Imagine\Exception\RuntimeException
     * @throws \ImageOptimizer\Exception\Exception
     */
    public function exec(IFile $file, $content)
    {
        $imagine = new Imagine();

        $image = \is_resource($content)
            ? $imagine->read($content)
            : $imagine->load($content);
        $imageSize = $image->getSize();

        $watermark = $this->getWatermark($imagine);
        $watermarkSize = $watermark->getSize();

        // NOTE: only for position: center
        $watermarkSize = $this->getWatermarkBox($imageSize, $watermarkSize);

        /* @see http://urmaul.com/blog/imagick-filters-comparison */
        $watermark = $watermark->resize($watermarkSize, ImageInterface::FILTER_SINC);
        $watermarkSize = $watermark->getSize();

        $image = $image->paste($watermark, $this->getPositionPoint($imageSize, $watermarkSize));

        return $image->get($file->getExtension() ?? Image::DEFAULT_EXTENSION);
    }

    /**
     * Get watermark image
     * @param Imagine $imagine
     * @return ImageInterface
     * @throws \Imagine\Exception\RuntimeException
     * @throws \UnexpectedValueException
     */
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

    /**
     * @param BoxInterface $imageSize
     * @param BoxInterface $watermarkSize
     *
     * @return BoxInterface
     */
    private function getWatermarkBox(BoxInterface $imageSize, BoxInterface $watermarkSize): BoxInterface
    {
        // ensure that watermark smaller than image
        $watermarkSize = $watermarkSize->widen($imageSize->getWidth() * $this->scale);
        $watermarkSize = $watermarkSize->heighten(min($watermarkSize->getHeight(), $imageSize->getHeight()));

        return $watermarkSize;
    }

    /**
     * @param BoxInterface $imageSize
     * @param BoxInterface $watermarkSize
     *
     * @return Point
     * @throws \Imagine\Exception\InvalidArgumentException
     */
    private function getPositionPoint(BoxInterface $imageSize, BoxInterface $watermarkSize): Point
    {
        return new Point(
            ($imageSize->getWidth() - $watermarkSize->getWidth()) / 2,
            ($imageSize->getHeight() - $watermarkSize->getHeight()) / 2
        );
    }
}