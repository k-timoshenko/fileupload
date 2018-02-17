<?php
declare(strict_types=1);

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
    public const IMAGE_HD = 'hd';
    public const IMAGE_FULL_HD = 'full_hd';

    // Default file formatters
    public const FILE_DEFAULT_FORMAT = self::FILE_ORIGINAL;
    public const IMAGE_DEFAULT_FORMAT = self::IMAGE_HD;

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

        self::IMAGE_HD => [
            'class' => Image::class,
            'width' => 1280,
            'height' => 720,
        ],
        self::IMAGE_FULL_HD => [
            'class' => Image::class,
            'width' => 1920,
            'height' => 1080,
        ],
    ];

    /**
     * Configs for available formatters.
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
     * @param FilesystemInterface $contentFS
     * @param string $key
     * @param array $formatterConfig
     * @return File
     * @throws \RuntimeException
     */
    public function build(IFile $file, Alias $alias, FilesystemInterface $contentFS, string $key, array $formatterConfig = []): File
    {
        $defaultFormatterConfig = $this->formatterConfigArray[$key] ?? null;
        if ($defaultFormatterConfig === null) {
            throw new \RuntimeException(sprintf('Formatter for key `%s` not found', $key));
        }

        $class = null;
        $params = [];

        if (\is_array($defaultFormatterConfig)) {
            $class = $defaultFormatterConfig['class'];
            unset($defaultFormatterConfig['class']);
            $params = $defaultFormatterConfig;
        } elseif (\is_string($defaultFormatterConfig)) {
            $class = $defaultFormatterConfig;
        }

        $params = $formatterConfig + $params;

        $params['name'] = $key;
        $params['path'] = $alias->getFilePath($file);

        /* @see File::__construct() */
        return new $class($file, $contentFS, $params);
    }
}