<?php

namespace tkanstantsin\fileupload\config\formatter;

use tkanstantsin\fileupload\model\Type;

/**
 * Class FormatHelper
 * @todo: create real factory with changeable configuration.
 */
class Factory
{
    // File formatter constants
    public const FILE_ORIGINAL = '_original';
    public const IMAGE_LARGE = '_large';
    public const IMAGE_FULL_HD = '_full_hd';

    // Default file formatters
    public const FILE_DEFAULT_FORMAT = self::FILE_ORIGINAL;
    public const IMAGE_DEFAULT_FORMAT = self::IMAGE_LARGE;

    /**
     * Default set of configs for images. It can be supplemented in config file.
     * For new formatter add element if following format:
     * - key - unique constant;
     * - value - fields for one of `formatter\config\*.
     *
     * @var array
     */
    public static $defaultFormatterArray = [
        self::FILE_ORIGINAL => [
            'fileTypeId' => Type::FILE,
        ],

        self::IMAGE_LARGE => [
            'width' => 1280,
            'height' => 1024,
            'fileTypeId' => Type::IMAGE,
        ],
        self::IMAGE_FULL_HD => [
            'width' => 1920,
            'height' => 1080,
            'fileTypeId' => Type::IMAGE,
        ],
    ];

    /**
     * Associative array of file types with their formatters
     * @var array
     */
    public static $fileTypeToConfigArray = [
        Type::FILE => File::class,
        Type::IMAGE => Image::class,
        Type::DOC => File::class,
        Type::VIDEO => File::class,
        Type::AUDIO => File::class,
    ];

    /**
     * @param int $type
     * @param array $config
     * @return File
     * @throws \ErrorException
     */
    public static function createFormatConfig(int $type, array $config): File
    {
        $class = static::getConfigClass($type);

        return new $class($config);
    }

    /**
     * @param int $type
     * @param array $config
     * @return File
     * @throws \ErrorException
     */
    public function build(int $type, array $config): File
    {
        $class = static::getConfigClass($type);

        return new $class($config);
    }

    /**
     * @param int $type
     * @return string
     * @throws \ErrorException
     */
    public static function getConfigClass(int $type): string
    {
        $class = static::$fileTypeToConfigArray[$type] ?? null;
        if ($class === null) {
            throw new \ErrorException(sprintf('Type `%s` not found.', $type));
        }

        return $class;
    }

    /**
     * @see Type
     * @param int $fileType
     * @return string
     */
    public static function getDefaultFormat(int $fileType): string
    {
        switch ($fileType) {
            case Type::IMAGE:
                return static::IMAGE_DEFAULT_FORMAT;
            case Type::FILE:
            default:
                return static::FILE_DEFAULT_FORMAT;
        }
    }
}
