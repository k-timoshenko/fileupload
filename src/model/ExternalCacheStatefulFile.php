<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\model;

/**
 * Class ExternalCacheStatefulFile
 */
class ExternalCacheStatefulFile extends ExternalFile implements ICacheStateful
{
    /**
     * @todo: formalize cache config.
     * @xample (current)
     * ```php
     *  [
     *      'formatter-name' => 123456789,
     *      ...
     *  ],
     * ```
     * @example (for todo)
     * ```php
     *  [
     *      'formatter-name' => [
     *          'is_cached' => true|false,
     *          'cached_at' => 123456789,
     *          'empty' => true|false,
     *          'error' => true|false,
     *      ],
     *      ...
     *  ],
     * ```
     * @var array
     */
    private $cachedState = [];
    /**
     * @var \Closure
     */
    private $saveCachedStateCallback;

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
     * @param int|null $cachedAt
     */
    public function setCachedAt(string $format, ?int $cachedAt): void
    {
        $this->cachedState[$format] = $cachedAt;
        if ($cachedAt === null || $cachedAt <= 0) {
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

    /**
     * @param \Closure $callback
     */
    public function setSaveCacheCallback(\Closure $callback): void
    {
        $this->saveCachedStateCallback = $callback;
    }

    /**
     * @return bool
     */
    public function saveCachedState(): bool
    {
        if (!($this->saveCachedStateCallback instanceof \Closure)) {
            return true;
        }

        return (bool) \call_user_func($this->saveCachedStateCallback, $this);
    }
}