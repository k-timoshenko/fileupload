<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 9/29/16
 * Time: 2:49 PM
 */

namespace tkanstantsin\fileupload;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use tkanstantsin\fileupload\model\IFile;
use tkanstantsin\fileupload\formatter\File;

/**
 * Class Cache allows store files processed files.
 * E.g. store cropped image in web-accessible folder.
 */
class CacheComponent
{
    /**
     * @var IFile
     */
    public $file;
    /**
     * Filesystem where file will be stored
     * @var FilesystemInterface
     */
    public $filesystem;
    /**
     * File path in Cache::$filesystem
     * @var string
     */
    public $assetPath;

    /**
     * CacheComponent constructor.
     * @param IFile $file
     * @param Filesystem $filesystem
     * @param string $assetPath
     */
    public function __construct(IFile $file, Filesystem $filesystem, string $assetPath)
    {
        $this->file = $file;
        $this->filesystem = $filesystem;
        $this->assetPath = $assetPath;
    }

    /**
     * Copies, processes and saves file in Cache::$filesystem
     * @todo: add cleanup of cached files.
     * @param \tkanstantsin\fileupload\formatter\File $formatter
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function cache(File $formatter): bool
    {
        if ($this->isCached()) {
            return true;
        }

        // checks if path is writable
        // create new empty file or override existed one
        // also caches empty result for non-formatted files
        $this->filesystem->put($this->assetPath, null);

        return $this->saveIntoCache($formatter->getContent());
    }

    /**
     * Checks if file is already in cache.
     * @return bool
     * @throws \League\Flysystem\FileNotFoundException
     */
    protected function isCached(): bool
    {
        return $this->filesystem->has($this->assetPath)
            && $this->filesystem->getTimestamp($this->assetPath) > $this->file->getUpdatedAt();
    }

    /**
     * Saves file into Cache::$filesystem
     * @param $content
     * @return bool
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \InvalidArgumentException
     */
    protected function saveIntoCache($content): bool
    {
        if ($content === false || $content === null) {
            return false;
        }

        // remove old file
        if ($this->filesystem->has($this->assetPath) && !$this->filesystem->delete($this->assetPath)) {
            return false;
        }

        return \is_resource($content)
            ? $this->filesystem->putStream($this->assetPath, $content)
            : $this->filesystem->put($this->assetPath, $content);
    }
}