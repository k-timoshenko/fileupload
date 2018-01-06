<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\formatter;

use Imagine\Image\Box;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\Palette\RGB as RGBPalette;
use Imagine\Image\Point;
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
    /**
     * Means that image may be smaller than defined in config, never bigger.
     */
    public const RESIZE_INSET_KEEP_RATIO = 'inset_keep_ratio';

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
     * Used when defined only height as upper limit for width
     * @todo: implement in Image::createBox() method.
     * @var int
     */
    public $maxWidth;
    /**
     * Used when defined only widith as upper limit for height
     * @var int
     */
    public $maxHeight;

    /**
     * Used for jpg images which may be png originally and have transparency.
     * @var string
     */
    public $transparentBackground = 'ffffff';

    /**
     * @var string
     */
    public $mode = self::RESIZE_INSET;
    /**
     * Whether image must keep aspect ration when used inset mote.
     * Means that image would may be smaller than smaller
     * @var bool
     */
    public $keepRatio = true;

    /**
     * @var Imagine
     */
    protected $imagine;

    /**
     * @inheritdoc
     * @throws \Imagine\Exception\RuntimeException
     */
    public function init(): void
    {
        parent::init();

        $this->imagine = new Imagine();
    }

    /**
     * @inheritdoc
     * @throws \UnexpectedValueException
     * @throws \Imagine\Exception\InvalidArgumentException
     * @throws \Imagine\Exception\RuntimeException
     */
    protected function getContentInternal()
    {
        $image = $this->imagine->read(parent::getContentInternal());
        $image = $this->format($image);

        return $image->get($this->getExtension());
    }

    /**
     * @return string
     */
    protected function getExtension(): string
    {
        return mb_strtolower($this->file->getExtension() ?? self::DEFAULT_EXTENSION);
    }

    /**
     * @param ImageInterface $image
     * @return ImageInterface
     * @throws \UnexpectedValueException
     * @throws \Imagine\Exception\RuntimeException
     * @throws \Imagine\Exception\InvalidArgumentException
     */
    protected function format(ImageInterface $image): ImageInterface
    {
        $image = $this->resize($image);
        $image = $this->setBackground($image);

        return $image;
    }

    /**
     * @param ImageInterface $image
     * @return ImageInterface
     * @throws \Imagine\Exception\RuntimeException
     * @throws \UnexpectedValueException
     * @throws \Imagine\Exception\InvalidArgumentException
     */
    protected function resize(ImageInterface $image): ImageInterface
    {
        $box = $this->createBox($image->getSize());
        if ($box === null) {
            return $image;
        }

        switch ($this->mode) {
            case self::RESIZE_OUTBOUND:
            case self::RESIZE_INSET:
                return $image->thumbnail($box, $this->mode);
            case self::RESIZE_INSET_KEEP_RATIO:
                // TODO: implement new resize mode.
                throw new \UnexpectedValueException(sprintf('Resize mode `%s` not supported yet', $this->mode));
            default:
                throw new \UnexpectedValueException(sprintf('Image resize mode `%s` not defined', $this->mode));
        }
    }

    /**
     * @param BoxInterface $actualBox
     * @return BoxInterface|null
     * @throws \Imagine\Exception\InvalidArgumentException
     */
    protected function createBox(BoxInterface $actualBox): ?BoxInterface
    {
        if ($this->width !== null
            && $this->height !== null
        ) {
            return new Box($this->width, $this->height);
        }

        if ($this->width !== null) {
            $box = $actualBox->widen($this->width);
            if ($this->maxHeight !== null && $this->maxHeight < $box->getHeight()) {
                $box = $box->heighten($this->maxHeight);
            }

            return $box;
        }
        if ($this->height !== null) {
            $box = $actualBox->heighten($this->height);
            if ($this->maxWidth !== null && $this->maxWidth < $box->getWidth()) {
                $box = $box->widen($this->maxWidth);
            }

            return $box;
        }

        // both are null
        return null;
    }

    /**
     * Add Image::transparentBackground color behind image.
     * If original image was of PNG type but stored with jpg extension, or it
     * must be converted
     *
     * @param ImageInterface $image
     * @return ImageInterface
     * @throws \Imagine\Exception\RuntimeException
     * @throws \Imagine\Exception\InvalidArgumentException
     */
    protected function setBackground(ImageInterface $image): ImageInterface
    {
        if (!\in_array($this->getExtension(), ['jpg', 'jpeg'], true)) {
            return $image;
        }

        $palette = new RGBPalette();
        $backgroundColor = $palette->color($this->transparentBackground, 100);
        $background = $this->imagine->create($image->getSize(), $backgroundColor);

        return $background->paste($image, new Point(0, 0));
    }
}