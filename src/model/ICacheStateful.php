<?php

namespace tkanstantsin\fileupload\model;

use tkanstantsin\fileupload\formatter\Factory;

interface ICacheStateful
{
    /**
     * @param string $format
     * @see Factory::$formatterConfigArray
     * @return int
     */
    public function getCachedAt(string $format): int;

    /**
     * @param string $format
     * @param int $cachedAt
     */
    public function setCachedAt(string $format, int $cachedAt): void;

    /**
     * @param string $format
     * @return bool
     */
    public function getIsCached(string $format): bool;

    /**
     * @param string $format
     * @param bool $isCached
     */
    public function setIsCached(string $format, bool $isCached): void;
}