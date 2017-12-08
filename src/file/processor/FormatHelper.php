<?php

namespace tkanstantsin\fileupload\processor;

use tkanstantsin\fileupload\model\Type;

/**
 * Class FormatHelper
 */
class FormatHelper
{
    // Image sizes configs
    public const FILE_ORIGINAL = '_original';
    public const IMAGE_NORMAL = '_normal';
    public const IMAGE_LARGE = '_large';
    public const IMAGE_FULL_HD = '_full_hd';
    public const IMAGE_UPLOAD_PREVIEW = '_upload_preview';

    // Default image size config
    public const IMAGE_DEFAULT_FORMAT = self::IMAGE_NORMAL;
    public const FILE_DEFAULT_FORMAT = self::FILE_ORIGINAL;

    /**
     * Default set of configs for images. It can be supplemented in config file.
     * For legacy configs you should add: ```LEGACY_SIZE_CONST => ACTUAL_SIZE_CONST,```
     * To add size config you should add new element in config file. Key must be new created constant.
     * Value must be associative array which key are property names of [[ImageConfig]] class.
     * If you want override one of default config,
     * you should add in config element with key from one of default constants.
     *
     * @var array
     */
    public static $defaultImageSizesConfig = [
        FormatHelper::FILE_ORIGINAL => [
            'fileTypeId' => Type::FILE,
        ],
        FormatHelper::IMAGE_NORMAL => [
            'width' => 800,
            'height' => 600,
            'fileTypeId' => Type::IMAGE,
        ],
        FormatHelper::IMAGE_LARGE => [
            'width' => 1280,
            'height' => 1024,
            'fileTypeId' => Type::IMAGE,
        ],
        FormatHelper::IMAGE_FULL_HD => [
            'width' => 1920,
            'height' => 1080,
            'fileTypeId' => Type::IMAGE,
        ],

        FormatHelper::IMAGE_UPLOAD_PREVIEW => [
            'width' => 100,
            'height' => 100,
            'fileTypeId' => Type::IMAGE,
        ],
    ];

    public static $typeToConfigClass = [
        Type::FILE => FileConfig::class,
        Type::IMAGE => ImageConfig::class,
    ];

    /**
     * @param int $type
     * @param array $config
     * @return FileConfig
     * @throws \ErrorException
     */
    public static function createFormatConfig(int $type, array $config): FileConfig
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
        $class = static::$typeToConfigClass[$type] ?? null;
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
    public static function getDefaultFormatByType(int $fileType): string
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
