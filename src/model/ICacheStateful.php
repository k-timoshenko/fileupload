<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\model;

use tkanstantsin\fileupload\formatter\Factory;

interface ICacheStateful
{
    /**
     * Set all cached formats
     * @example:
     * ```php
     * [
     *      'format-name' => `cached at unixtimestamp`,
     *      'another-format-name' => `cached at unixtimestamp`,
     * ]
     * ```
     * @param array $cachedState
     */
    public function setCachedStateArray(array $cachedState): void;

    /**
     * Get all cached formats data
     * @return array
     */
    public function getCachedStateArray(): array;

    /**
     * Get caching time of single format
     * @param string $format
     * @see Factory::$formatterConfigArray
     * @return int
     */
    public function getCachedAt(string $format): ?int;

    /**
     * Set caching time of single format
     * @param string $format
     * @param int $cachedAt
     */
    public function setCachedAt(string $format, int $cachedAt): void;

    /**
     * Whether file already cached in such format
     * @param string $format
     * @return bool
     */
    public function getIsCached(string $format): bool;
}