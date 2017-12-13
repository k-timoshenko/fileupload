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
use tkanstantsin\fileupload\config\formatter\Image as ImageConfig;

/**
 * Class ImageProcessor
 *
 * @property ImageConfig $formatConfig
 */
class Image extends File
{
    public const DEFAULT_EXTENSION = 'jpg';

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException
     */
    public function init(): void
    {
        parent::init();

        if (!($this->formatConfig instanceof ImageConfig)) {
            throw new \InvalidArgumentException('Format config must be instance of `' . ImageConfig::class . '`');
        }
    }

    /**
     * @inheritdoc
     * @throws \Imagine\Exception\InvalidArgumentException
     * @throws \Imagine\Exception\RuntimeException
     */
    public function getContentInternal()
    {
        $image = (new Imagine())->read($this->filesystem->readStream($this->path));
        $box = $this->formatConfig->width !== null && $this->formatConfig->height !== null
            ? new Box($this->formatConfig->width, $this->formatConfig->height)
            : $image->getSize(); // means don't change image size
        $thumb = $image->thumbnail($box, $this->formatConfig->mode);

        return $thumb->get($this->file->getExtension() ?? self::DEFAULT_EXTENSION);
    }
}