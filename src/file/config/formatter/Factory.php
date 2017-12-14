<?php

namespace tkanstantsin\fileupload\config\formatter;


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
            'class' => File::class,
        ],

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
     * @var array
     */
    protected $formatterConfigArray;
    /**
     * @var File[]
     */
    protected $formatterArray = [];

    /**
     * Create instance of factory
     * @param array $config
     * @return Factory
     */
    public static function build(array $config): self
    {
        return new static($config);
    }

    /**
     * Factory constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->formatterConfigArray = $config + static::$defaultFormatterArray;
    }

    /**
     * @param string $format
     * @return File
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function getConfig(?string $format): File
    {
        $format = $format ?? static::FILE_DEFAULT_FORMAT;

        $formatter = $this->formatterArray[$format] ?? null;
        if ($formatter === null) {
            $formatterConfig = $this->formatterConfig[$format] ?? null;
            if ($formatterConfig === null) {
                throw new \RuntimeException(sprintf('Format `%s` not found in config.', $format));
            }

            $class = $formatterConfig['class'];
            unset($formatterConfig['class']);

            $formatter = new $class($formatterConfig);
            $this->formatterArray[$format] = $formatter;
        }

        return $formatter;
    }
}
