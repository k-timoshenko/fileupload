<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 5.7.17
 * Time: 12.41
 */

namespace tkanstantsin\fileupload\formatter;

use League\Flysystem\FilesystemInterface;
use tkanstantsin\fileupload\config\Alias;
use tkanstantsin\fileupload\model\IFile;

/**
 * Class Factory
 */
class Factory
{
    // File formatter constants
    public const FILE_ORIGINAL = 'original';
    public const IMAGE_LARGE = 'large';
    public const IMAGE_FULL_HD = 'full_hd';

    // Default file formatters
    public const FILE_DEFAULT_FORMAT = self::FILE_ORIGINAL;
    public const IMAGE_DEFAULT_FORMAT = self::IMAGE_LARGE;

    /**
     * Default set of configs for files and images. It can be supplemented in
     * config file. For new formatter add element if following format:
     * - key - unique constant;
     * - value - fields for one of `formatter\config\*.
     *
     * @var array
     */
    public const DEFAULT_FORMATTER_ARRAY = [
        self::FILE_ORIGINAL => File::class,

        self::IMAGE_LARGE => [
            'class' => Image::class,
            'width' => 1280,
            'height' => 1024,
        ],
        self::IMAGE_FULL_HD => [
            'class' => Image::class,
            'width' => 1920,
            'height' => 1080,
        ],
    ];

    /**
     * Configs for availabe formatters.
     * May contain: class name or array with class name and parameters.
     * @example: \tkanstantsin\fileupload\formatter\File
     * ```php
     * [
     *     \tkanstantsin\fileupload\formatter\File::class,
     *     'full_hd' => [
     *         'class' => \tkanstantsin\fileupload\formatter\Image::class,,
     *         'width' => 1920,
     *         'height' => 1080,
     *         'mode' =>
     *     \tkanstantsin\fileupload\formatter\Image::RESIZE_OUTBOUND,
     *         'formatAdapterArray' => [
     *             SomeAdapter::class,
     *             SecondAdapter::class,
     *         ],
     *     ],
     * ]
     * ```
     *
     * @var array
     */
    protected $formatterConfigArray;

    /**
     * Factory constructor.
     * @param array $formatterConfigArray
     */
    public function __construct(array $formatterConfigArray)
    {
        $this->formatterConfigArray = $formatterConfigArray + static::DEFAULT_FORMATTER_ARRAY;
    }

    /**
     * @param IFile $file
     * @param Alias $alias
     * @param FilesystemInterface $filesystem
     * @param string $key
     * @return File
     */
    public function build(IFile $file, Alias $alias, FilesystemInterface $filesystem, string $key): File
    {
        $formatterConfig = $this->formatterConfigArray[$key] ?? null;
        if ($formatterConfig === null) {
            throw new \RuntimeException(sprintf('Formatter for key `%s` not found', $key));
        }

        $class = null;
        $params = [];

        if (\is_array($formatterConfig)) {
            $class = $formatterConfig['class'];
            unset($formatterConfig['class']);
            $params = $formatterConfig;
        } elseif (\is_string($formatterConfig)) {
            $class = $formatterConfig;
        }

        $params['name'] = $key;
        $params['path'] = $alias->getFilePath($file);

        /* @see File::__construct() */
        return new $class($file, $filesystem, $params);
    }
}