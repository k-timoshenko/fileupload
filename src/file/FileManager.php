<?php

namespace tkanstantsin\fileupload;

use League\Flysystem\Filesystem;
use tkanstantsin\fileupload\config\Alias;
use tkanstantsin\fileupload\config\Factory as AliasFactory;
use tkanstantsin\fileupload\formatter\Factory as FormatterFactory;
use tkanstantsin\fileupload\formatter\File as FileFormatter;
use tkanstantsin\fileupload\formatter\icon\IconGenerator;
use tkanstantsin\fileupload\model\IFile;
use tkanstantsin\fileupload\model\Type;

/**
 * Class FileComponent
 */
class FileManager
{
    /**
     * Base url for uploading files
     * @var string
     */
    public $uploadBaseUrl;
    /**
     * Path to folder which would contain cached files.
     * /cache-base-path/alias/part-of-hash/file-name
     * @var string
     */
    public $cacheBasePath;

    /**
     * Url for default image
     * @var string|array
     */
    public $imageNotFoundUrl;
    /**
     * Url for 404 page for files
     * @var string|array
     */
    public $fileNotFoundUrl;

    /**
     * For uploading
     * @var Filesystem
     */
    public $uploadFS;
    /**
     * Web accessible FS
     * @var Filesystem
     */
    public $cacheFS;

    /**
     * @see config\model\Alias
     * @var array
     */
    public $aliasArray;
    /**
     * @see config\model\Alias
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
     * FileManagerComponent constructor.
     * @param array $config
     * @throws \ErrorException
     */
    public function __construct(array $config)
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }

        $this->init();
    }

    /**
     * Check initialization parameters and parse configs
     * @throws \ErrorException
     */
    public function init(): void
    {
        if (!($this->uploadFS instanceof Filesystem)) {
            throw new \ErrorException(sprintf('UploadFS must be instance of %s.', Filesystem::class));
        }
        if (!($this->cacheFS instanceof Filesystem)) {
            throw new \ErrorException(sprintf('CacheFS must be instance of %s.', Filesystem::class));
        }
        if ($this->uploadBaseUrl === null) {
            throw new \ErrorException('Base upload url must be defined.');
        }
        if ($this->cacheBasePath === null) {
            throw new \ErrorException('Base path for cache must be defined.');
        }
        if ($this->imageNotFoundUrl === null || $this->fileNotFoundUrl === null) {
            throw new \ErrorException('URLs for not founded image and file must be defined.');
        }

        $this->iconGenerator = IconGenerator::build($this->iconSet);
        $this->formatterFactory = new FormatterFactory((array) $this->formatterConfigArray);
        $this->aliasFactory = AliasFactory::build($this->defaultAlias);
        $this->aliasFactory->addMultiple($this->aliasArray);
    }


    /* PROXY methods */

    /**
     * @param string $name
     * @return Alias
     * @throws \ErrorException
     */
    public function getAliasConfig(string $name): Alias
    {
        return $this->aliasFactory->getAliasConfig($name);
    }

    /**
     * @param string $name
     * @return string|null
     * @throws \ErrorException
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
     * @return PathBuilder
     * @throws \RuntimeException
     * @throws \ErrorException
     */
    public function getPathBuilder(IFile $file, string $format, array $formatterConfig = []): PathBuilder
    {
        $alias = $this->getAliasConfig($file->getModelAlias());
        $formatter = $this->formatterFactory->build($file, $alias, $this->uploadFS, $format, $formatterConfig);

        return new PathBuilder($file, $alias, $formatter);
    }

    /**
     * Generates url for upload file with upload widget.
     * Url format: $uploadBaseUrl/$alias/$id
     * @example /upload/product/555
     * @param string $aliasName
     * @param int $id of related model
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function getUploadUrl(string $aliasName, int $id = null): string
    {
        return implode(DIRECTORY_SEPARATOR, array_filter([$this->uploadBaseUrl, $aliasName, $id]));
    }

    /**
     * Caches file and returns url to it.
     * @param IFile $file
     * @param string $format
     * @param array $formatterConfig
     * @return string
     * @throws \RuntimeException
     * @throws \ErrorException
     */
    public function getFileUrl(IFile $file, string $format, array $formatterConfig = []): string
    {
        if ($file->getId()) {
            $pathBuilder = $this->getPathBuilder($file, $format, $formatterConfig);
            if ($this->cacheFile($file, $pathBuilder->alias, $pathBuilder->formatter)) {
                return implode(DIRECTORY_SEPARATOR, [
                    $this->cacheBasePath,
                    $pathBuilder->alias->getCachePath($file, $format)
                ]);
            }
        }

        return $this->getNotFoundUrl($file);
    }

    /**
     * Choose 404 url
     * @param IFile $file
     * @return string
     */
    public function getNotFoundUrl(IFile $file): string
    {
        switch ($file->getType()) {
            case Type::IMAGE:
                return $this->imageNotFoundUrl;
            default:
                return $this->fileNotFoundUrl;
        }
    }

    /**
     * Caches file available in web.
     *
     * @param IFile $file
     * @param Alias $alias
     * @param FileFormatter $formatter
     * @return bool
     * @internal param string $fileType
     */
    protected function cacheFile(IFile $file, Alias $alias, FileFormatter $formatter): bool
    {
        try {
            $fileCache = new CacheComponent($file, $this->cacheFS, $alias->getCachePath($file, $formatter->getName()));

            return $fileCache->cache($formatter);
        } catch (\Exception $e) {
            // Do nothing, just catch exception and keep executing.
            return false;
        }
    }

}
