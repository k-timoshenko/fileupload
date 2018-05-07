<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\formatter;

use Imagine\Image\Box;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Palette\RGB as RGBPalette;
use Imagine\Image\Point;
use tkanstantsin\fileupload\config\InvalidConfigException;

/**
 * Class ImageProcessor
 *
 * @todo: add min width|height options.
 */
class Image extends File
{
    /**
     * Like background `cover` in css.
     */
    public const RESIZE_OUTBOUND = ImageInterface::THUMBNAIL_OUTBOUND;
    /**
     * Like background `contain` in css.
     */
    public const RESIZE_INSET = ImageInterface::THUMBNAIL_INSET;
    /**
     * Means that image may be smaller than defined in config, never bigger.
     */
    public const RESIZE_INSET_KEEP_RATIO = 'inset_keep_ratio';

    public const DEFAULT_EXTENSION = 'jpg';

    /**
     * @see ImagineFactory::get()
     * @var string
     */
    public $driver = ImagineFactory::DEFAULT_DRIVER;

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
     * @todo: implement it.
     * @var bool
     */
    public $keepRatio = true;

    /**
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * @inheritdoc
     * @throws \Imagine\Exception\RuntimeException
     */
    public function init(): void
    {
        parent::init();

        $this->imagine = ImagineFactory::get($this->driver);

        if ($this->width !== null && $this->maxWidth !== null) {
            throw new InvalidConfigException('`width` and `maxWidth` cannot be defined at the same time');
        }
        if ($this->height !== null && $this->maxHeight !== null) {
            throw new InvalidConfigException('`height` and `maxHeight` cannot be defined at the same time');
        }
    }

    /**
     * @todo: add check for metadata with `exif_read_data` method.
     * @see http://php.net/manual/en/function.exif-read-data.php
     * @inheritdoc
     * @throws \Imagine\Exception\OutOfBoundsException
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
     * @throws \Imagine\Exception\OutOfBoundsException
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
        $originBox = $image->getSize();
        $newBox = $this->createBox($originBox);
        if ($this->areBoxesEqual($originBox, $newBox)) {
            return $image;
        }

        switch ($this->mode) {
            case self::RESIZE_OUTBOUND:
            case self::RESIZE_INSET:
                return $image->thumbnail($newBox, $this->mode);
            case self::RESIZE_INSET_KEEP_RATIO:
                // TODO: implement new resize mode.
                throw new \UnexpectedValueException(sprintf('Resize mode `%s` not supported yet', $this->mode));
            default:
                throw new \UnexpectedValueException(sprintf('Image resize mode `%s` not defined', $this->mode));
        }
    }

    /**
     * @param BoxInterface $actualBox
     * @return BoxInterface
     */
    protected function createBox(BoxInterface $actualBox): BoxInterface
    {
        // TODO: check resize modes.
        if ($this->width !== null && $this->height !== null) {
            return new Box($this->width, $this->height);
        }

        $box = clone $actualBox;
        if ($this->width !== null) {
            $box = $box->widen($this->width);
        }
        if ($this->height !== null) {
            $box = $box->heighten($this->height);
        }

        if ($this->maxWidth !== null && $this->maxWidth < $box->getWidth()) {
            $box = $box->widen($this->maxWidth);
        }
        if ($this->maxHeight !== null && $this->maxHeight < $box->getHeight()) {
            $box = $box->heighten($this->maxHeight);
        }

        return $box;
    }

    /**
     * Add Image::transparentBackground color behind image.
     * If original image was of PNG type but stored with jpg extension, or it
     * must be converted
     *
     * @param ImageInterface $image
     * @return ImageInterface
     * @throws \Imagine\Exception\OutOfBoundsException
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

    /**
     * @param BoxInterface $a
     * @param BoxInterface $b
     * @return bool
     */
    protected function areBoxesEqual(BoxInterface $a, BoxInterface $b): bool
    {
        return $a->getWidth() === $b->getWidth()
            && $a->getHeight() === $b->getHeight();
    }
}