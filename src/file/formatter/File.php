<?php

namespace tkanstantsin\fileupload\formatter;

use League\Flysystem\FilesystemInterface;
use tkanstantsin\fileupload\model\IFile;

/**
 * Class FileProcessor
 * TODO: create callbacks or interfaces for getting customized file name or
 * filepath
 */
class File
{
    /**
     * @see Factory::DEFAULT_FORMATTER_ARRAY
     * @example file, _normal, _product_preview
     * @var string
     */
    public $name;

    /**
     * @var IFile
     */
    protected $file;
    /**
     * Path to original file in contentFS
     * @var string
     */
    protected $path;
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var IFormatAdapter[]|array
     */
    protected $formatAdapterArray = [];

    /**
     * Additional dynamic config for processor class.
     * @var array
     */
    public $config = [];

    /**
     * FileProcessor constructor.
     * @param IFile $file
     * @param FilesystemInterface $filesystem
     * @param string $name
     * @param array $config
     * @throws \RuntimeException
     */
    public function __construct(IFile $file, FilesystemInterface $filesystem, string $name, array $config = [])
    {
        $this->file = $file;
        $this->filesystem = $filesystem;
        $this->name = $name;
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