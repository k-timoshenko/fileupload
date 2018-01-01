<?php

namespace tkanstantsin\fileupload;

use League\Flysystem\Filesystem;
use tkanstantsin\fileupload\config\Factory as AliasFactory;
use tkanstantsin\fileupload\config\InvalidConfigException;
use tkanstantsin\fileupload\formatter\Factory as FormatterFactory;
use tkanstantsin\fileupload\formatter\File as FileFormatter;
use tkanstantsin\fileupload\formatter\icon\IconGenerator;
use tkanstantsin\fileupload\model\BaseObject;
use tkanstantsin\fileupload\model\IFile;
use tkanstantsin\fileupload\saver\Saver;

/**
 * Class FileComponent
 */
class FileManager extends BaseObject
{
    /**
     * For uploading
     * @var Filesystem
     */
    public $contentFS;
    /**
     * Web accessible FS
     * @var Filesystem
     */
    public $cacheFS;

    /**
     * @see config\Alias
     * @var array
     */
    public $aliasArray;
    /**
     * @see config\Alias
     * @var array
     */
    public $defaultAlias = [
        'maxSize' => config\Alias::DEFAULT_MAX_SIZE,
        'maxCount' => config\Alias::DEFAULT_MAX_COUNT,
        'hashMethod' => config\Alias::DEFAULT_HASH_METHOD,
        'cacheHashLength' => config\Alias::DEFAULT_HASH_LENGTH,
    ];
    /**
     * @var AliasFactory
     */
    private $aliasFactory;

    /**
     * Icon set class for icons
     * @see formatter\icon\FontAwesome
     * @see formatter\icon\ElusiveIcons
     * @var string|null
     */
    public $iconSet;
    /**
     * @var IconGenerator
     */
    private $iconGenerator;

    /**
     * @var array
     */
    public $formatterConfigArray = [];
    /**
     * @var FormatterFactory
     */
    private $formatterFactory;

    /**
     * Check initialization parameters and parse configs
     * @throws config\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        if (!($this->contentFS instanceof Filesystem)) {
            throw new InvalidConfigException(sprintf('ContentFS must be instance of %s.', Filesystem::class));
        }
        if (!($this->cacheFS instanceof Filesystem)) {
            throw new InvalidConfigException(sprintf('CacheFS must be instance of %s.', Filesystem::class));
        }

        $this->iconGenerator = IconGenerator::build($this->iconSet);
        $this->formatterFactory = new FormatterFactory((array) $this->formatterConfigArray);
        $this->aliasFactory = AliasFactory::build($this->defaultAlias);
        $this->aliasFactory->addMultiple($this->aliasArray);
    }


    /* PROXY methods */

    /**
     * @param string $name
     * @return config\Alias
     * @throws \RuntimeException
     */
    public function getAliasConfig(string $name): config\Alias
    {
        return $this->aliasFactory->getAliasConfig($name);
    }

    /**
     * @param string $name
     * @return string|null
     * @throws \RuntimeException
     */
    public function getModelByAlias(string $name): ?string
    {
        return $this->getAliasConfig($name)->class ?? null;
    }

    /**
     * @see IconGenerator::getIcon()
     * @param null|string $extension
     * @return string
     */
    public function getIcon(?string $extension): ?string
    {
        return $this->iconGenerator->getIcon($extension);
    }


    /* OTHER methods */

    /**
     * @param IFile $file
     * @param string $format
     * @param array $formatterConfig
     * @return FileFormatter
     * @throws \RuntimeException
     */
    public function buildFormatter(IFile $file, string $format, array $formatterConfig = []): FileFormatter
    {
        $alias = $this->getAliasConfig($file->getModelAlias());

        return $this->formatterFactory->build($file, $alias, $this->contentFS, $format, $formatterConfig);
    }

    /**
     * Caches file and returns url to it.
     * @param IFile $file
     * @param string $format
     * @param array $formatterConfig
     * @return string
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function getFilePath(IFile $file, string $format, array $formatterConfig = []): string
    {
        $alias = $this->getAliasConfig($file->getModelAlias());
        $formatter = $this->buildFormatter($file, $format, $formatterConfig);

        if (!$this->cacheFile($file, $alias, $formatter)) {
            return $format;
        }

        return $alias->getCachePath($file, $format);
    }

    /**
     * Caches file available in web.
     *
     * @param IFile $file
     * @param config\Alias $alias
     * @param FileFormatter $formatter
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \League\Flysystem\FileNotFoundException
     */
    protected function cacheFile(IFile $file, config\Alias $alias, FileFormatter $formatter): bool
    {
        $path = $alias->getCachePath($file, $formatter->getName());

        return (new Saver($file, $this->cacheFS, $path))->save($formatter);
    }

}
