<?php

namespace tkanstantsin\fileupload\saver;

use League\Flysystem\FilesystemInterface;
use tkanstantsin\fileupload\formatter\File;
use tkanstantsin\fileupload\model\IFile;

/**
 * Class Saver allows store processed files.
 * E.g. store uploaded files or cache cropped/prepared images.
 */
class Saver
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
     * File path in $filesystem
     * @var string
     */
    public $path;

    /**
     * Saver constructor.
     * @param IFile $file
     * @param FilesystemInterface $filesystem
     * @param string $assetPath
     */
    public function __construct(IFile $file, FilesystemInterface $filesystem, string $assetPath)
    {
        $this->file = $file;
        $this->filesystem = $filesystem;
        $this->path = $assetPath;
    }

    /**
     * Copies, processes and saves file in $filesystem
     * @param \tkanstantsin\fileupload\formatter\File $formatter
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function save(File $formatter): bool
    {
        if ($this->isSaved()) {
            return true;
        }

        // checks if path is writable
        // create new empty file or override existed one
        // also caches empty result for non-formatted files
        $this->filesystem->put($this->path, null);

        return $this->write($formatter->getContent());
    }

    /**
     * Checks if file is already in $path.
     * @return bool
     * @throws \League\Flysystem\FileNotFoundException
     */
    protected function isSaved(): bool
    {
        return $this->filesystem->has($this->path)
            && $this->filesystem->getTimestamp($this->path) > $this->file->getUpdatedAt();
    }

    /**
     * Saves file into $filesystem
     * @param $content
     * @return bool
     * @throws \InvalidArgumentException
     */
    protected function write($content): bool
    {
        if ($content === false || $content === null) {
            return false;
        }

        return \is_resource($content)
            ? $this->filesystem->putStream($this->path, $content)
            : $this->filesystem->put($this->path, $content);
    }
}