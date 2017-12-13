<?php

namespace tkanstantsin\fileupload\config\model;

/**
 * Class Alias represent config for model
 * @todo: add `accept` type.
 */
class Alias
{
    public const DEFAULT_MAX_SIZE = 5 * 1000 * 1000; // 5 MB
    public const DEFAULT_MAX_COUNT = 5;
    public const DEFAULT_HASH_LENGTH = 3;
    /**
     * @see hash_algos()
     */
    public const DEFAULT_HASH_METHOD = 'crc32'; // not crypto-strong, but fast (as needed)

    /**
     * Alias name
     * @var string
     */
    public $name;

    /**
     * Owner model class
     * @var string
     */
    public $class;
    /**
     * @var string
     */
    public $directory;

    /**
     * Max file size in bytes
     * @var int
     */
    public $maxSize;
    /**
     * Max file count
     * @var int
     */
    public $maxCount;
    /**
     * Whether it is allowed multiple files for one model
     * @var bool
     */
    public $multiple;

    /**
     * Hash method like crc32 or md5
     * @var string
     */
    public $hashMethod;
    /**
     * Length of hash generated by hash method
     * @var int
     */
    public $cacheHashLength;


    /**
     * Alias constructor.
     * @param array $config
     * @throws \ErrorException
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }

        if (!\is_int($this->maxSize) || $this->maxSize <= 0) {
            throw new \ErrorException(sprintf('Maximum file size must be positive integer but `%s` got.', $this->maxSize));
        }
        if (!\is_int($this->maxCount) || $this->maxCount <= 0) {
            throw new \ErrorException(sprintf('Maximum file count must be positive integer but `%s` got.', $this->maxCount));
        }
        if (!\in_array($this->hashMethod, hash_algos(), true)) {
            throw new \ErrorException(sprintf('Hash method `%s` not found.', $this->hashMethod));
        }
    }
}