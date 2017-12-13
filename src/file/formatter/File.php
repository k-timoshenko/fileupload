<?php

namespace tkanstantsin\fileupload\formatter;

use League\Flysystem\FilesystemInterface;
use tkanstantsin\fileupload\model\IFile;
use tkanstantsin\fileupload\config\formatter\File as FileConfig;

/**
 * Class FileProcessor
 */
class File
{
    /**
     * @var IFile
     */
    public $file;
    /**
     * Path to original file in contentFS
     * @var string
     */
    public $path;
    /**
     * @var FilesystemInterface
     */
    public $filesystem;
    /**
     * @var FileConfig
     */
    public $formatConfig;

    /**
     * Additional dynamic config for processor class.
     * @var array
     */
    public $config = [];

    /**
     * FileProcessor constructor.
     * @param IFile $file
     * @param FilesystemInterface $filesystem
     * @param array $config
     * @throws \ErrorException
     */
    public function __construct(IFile $file, FilesystemInterface $filesystem, array $config = [])
    {
        $this->file = $file;
        $this->filesystem = $filesystem;
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }

        $this->init();
    }

    /**
     * @inheritdoc
     * @throws \ErrorException
     */
    public function init(): void
    {
        if ($this->path === null) {
            throw new \ErrorException('File path property must be defined and be not empty');
        }
    }

    /**
     * @param FileConfig $config
     */
    public function setFormatConfig(FileConfig $config): void
    {
        $this->formatConfig = $config;
    }

    /**
     * @return resource|string|bool
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function getContent()
    {
        return $this->filesystem->has($this->path)
            ? $this->getContentInternal()
            : false;
    }

    /**
     * @return resource|bool
     * @throws \League\Flysystem\FileNotFoundException
     */
    protected function getContentInternal()
    {
        return $this->filesystem->readStream($this->path);
    }
}