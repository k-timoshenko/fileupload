<?php

namespace tkanstantsin\fileupload\formatter;

use League\Flysystem\FilesystemInterface;
use tkanstantsin\fileupload\model\BaseObject;
use tkanstantsin\fileupload\model\IFile;

/**
 * Class FileProcessor
 * TODO: create callbacks or interfaces for getting customized file name or
 * filepath
 */
class File extends BaseObject
{
    /**
     * @var IFile
     */
    protected $file;
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @see Factory::DEFAULT_FORMATTER_ARRAY
     * @example file, _normal, _product_preview
     * @var string
     */
    protected $name;

    /**
     * Path to original file in contentFS
     * @var string
     */
    protected $path;

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
     * @param array $config
     * @throws \RuntimeException
     */
    public function __construct(IFile $file, FilesystemInterface $filesystem, array $config = [])
    {
        $this->file = $file;
        $this->filesystem = $filesystem;
        parent::__construct($config);

        $this->init();
    }

    /**
     * Initialize app
     * @throws \RuntimeException
     */
    public function init(): void
    {
        parent::init();

        if ($this->name === null || !\is_string($this->name) || $this->name === '') {
            throw new \RuntimeException('Formatter name must be defined');
        }
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
            $content = $formatAdapter->exec($this->file, $content);
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