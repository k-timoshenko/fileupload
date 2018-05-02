<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\model;

class ExternalCacheStatefulFile extends ExternalFile implements ICacheStateful
{
    /**
     * @var array
     */
    private $cachedState = [];

    /**
     * @param array $cachedState
     */
    public function setCachedStateArray(array $cachedState): void
    {
        $this->cachedState = $cachedState;
    }

    /**
     * @return array
     */
    public function getCachedStateArray(): array
    {
        return $this->cachedState;
    }

    /**
     * @param string $format
     *
     * @see Factory::$formatterConfigArray
     * @return int
     */
    public function getCachedAt(string $format): ?int
    {
        $cachedAt = $this->cachedState[$format] ?? null;
        if ($cachedAt <= 0) {
            $cachedAt = null;
        }

        return $cachedAt;
    }

    /**
     * @param string $format
     * @param int $cachedAt
     */
    public function setCachedAt(string $format, int $cachedAt): void
    {
        $this->cachedState[$format] = $cachedAt;
        if ($cachedAt <= 0) {
            unset($this->cachedState[$format]);
        }
    }

    /**
     * @param string $format
     *
     * @return bool
     */
    public function getIsCached(string $format): bool
    {
        return $this->getCachedAt($format) !== null;
    }
}