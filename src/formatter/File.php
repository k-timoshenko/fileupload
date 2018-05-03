<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\formatter;

use League\Flysystem\FilesystemInterface;
use tkanstantsin\fileupload\config\InvalidConfigException;
use tkanstantsin\fileupload\formatter\adapter\IFormatAdapter;
use tkanstantsin\fileupload\model\BaseObject;
use tkanstantsin\fileupload\model\Container;
use tkanstantsin\fileupload\model\ICacheStateful;
use tkanstantsin\fileupload\model\IFile;

/**
 * Class FileProcessor
 * @todo: create callbacks or interfaces for getting customized file name
 *     or file path
 */
class File extends BaseObject
{
    public const EVENT_CACHED = 'cached';
    public const EVENT_EMPTY = 'empty';
    public const EVENT_ERROR = 'error';
    public const EVENT_NOT_FOUND = 'not_found';

    /**
     * Additional dynamic config for processor class.
     * @var array
     */
    public $config = [];
    /**
     * @var IFormatAdapter[]|array
     */
    public $formatAdapterArray = [];

    /**
     * @see Factory::DEFAULT_FORMATTER_ARRAY
     * @example file, _normal, _product_preview
     * @var string
     */
    public $name;
    /**
     * Path to original file in contentFS
     * @var string
     */
    public $path;

    /**
     * @var IFile
     */
    protected $file;
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * FileProcessor constructor.
     * @param IFile $file
     * @param FilesystemInterface $filesystem
     * @param array $config
     * @throws \tkanstantsin\fileupload\config\InvalidConfigException
     * @throws \ReflectionException
     */
    public function __construct(IFile $file, FilesystemInterface $filesystem, array $config = [])
    {
        $this->file = $file;
        $this->filesystem = $filesystem;
        parent::__construct($config);
    }

    /**
     * Initialize app
     * @throws InvalidConfigException
     * @throws \ReflectionException
     */
    public function init(): void
    {
        parent::init();

        if ($this->name === null || !\is_string($this->name) || $this->name === '') {
            throw new InvalidConfigException('Formatter name must be defined');
        }
        if ($this->path === null) {
            throw new InvalidConfigException('File path property must be defined and be not empty');
        }
        foreach ($this->formatAdapterArray as $i => $formatAdapter) {
            $this->formatAdapterArray[$i] = Container::createObject($formatAdapter);

            if (!($this->formatAdapterArray[$i] instanceof IFormatAdapter)) {
                throw new InvalidConfigException(sprintf('Format adapter must be instance of %s.', IFormatAdapter::class));
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
     * Call user function after saving cached file
     * @param string $event
     */
    public function triggerEvent(string $event): void
    {
        // TODO: use event library and allow attach events to IFile and ICacheStateful.
        // cache triggers
        if ($this->file instanceof ICacheStateful) {
            switch ($event) {
                case self::EVENT_CACHED:
                    $this->file->setCachedAt($this->name, time());
                    break;
                case self::EVENT_EMPTY:
                case self::EVENT_ERROR:
                case self::EVENT_NOT_FOUND:
                    // TODO: split those and save different cases of cache state.
                    $this->file->setCachedAt($this->name, null);
                    break;
            }

            $this->file->saveCachedState();
        }

        // other triggers
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