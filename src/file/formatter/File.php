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
     * @var IFormatAdapter[]|array
     */
    public $formatAdapterArray = [];

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
     * @throws \RuntimeException
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
     * Initialize app
     * @throws \RuntimeException
     */
    public function init(): void
    {
        if ($this->path === null) {
            throw new \RuntimeException('File path property must be defined and be not empty');
        }
        foreach ($this->formatAdapterArray as $key => $formatAdapter) {
            if (\is_string($formatAdapter) && class_exists($formatAdapter)) {
                $this->formatAdapterArray[$key] = new $formatAdapter;
            }
            if (!($formatAdapter instanceof IFormatAdapter)) {
                throw new \RuntimeException(sprintf('Format adapter must be instance of %s.', IFormatAdapter::class));
            }
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
        if (!$this->filesystem->has($this->path)) {
            return false;
        }

        $content = $this->getContentInternal();
        foreach ($this->formatAdapterArray as $formatAdapter) {
            $content = $formatAdapter->exec($content);
        }

        return $content;
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