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
use tkanstantsin\fileupload\processor\FileProcessor;

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
     * @param \tkanstantsin\fileupload\processor\FileProcessor $processor
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function cache(FileProcessor $processor): bool
    {
        if ($this->isCached()) {
            return true;
        }

        return $this->saveIntoCache($processor->getContent());
    }

    /**
     * Checks if file is already in cache.
     * @return bool
     * @throws \League\Flysystem\FileNotFoundException
     */
    protected function isCached(): bool
    {
        return $this->filesystem->has($this->assetPath) && $this->filesystem->getTimestamp($this->assetPath) > $this->file->getUpdatedAt();
    }

    /**
     * Saves file into Cache::$filesystem
     * @param $content
     * @return bool
     * @throws \League\Flysystem\FileExistsException
     * @throws \InvalidArgumentException
     * @throws \League\Flysystem\FileNotFoundException
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
            ? $this->filesystem->writeStream($this->assetPath, $content)
            : $this->filesystem->write($this->assetPath, $content);
    }
}