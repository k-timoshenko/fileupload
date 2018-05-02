<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\model;

use tkanstantsin\fileupload\formatter\Factory;

interface ICacheStateful
{
    /**
     * Get caching time of single format
     * @param string $format
     * @see Factory::$formatterConfigArray
     * @return int
     */
    public function getCachedAt(string $format): ?int;

    /**
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

    /**
     * @return bool
     */
    public function saveCachedState(): bool;
}