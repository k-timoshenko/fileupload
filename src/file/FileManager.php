<?php

namespace tkanstantsin\fileupload;

use League\Flysystem\Filesystem;
use tkanstantsin\fileupload\config\model\Alias;
use tkanstantsin\fileupload\formatter\icon\IconGenerator;
use tkanstantsin\fileupload\config\formatter\Factory as FormatterConfigFactory;
use tkanstantsin\fileupload\config\model\Factory as AliasConfigFactory;
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
        'maxSize' => config\model\Alias::DEFAULT_MAX_SIZE,
        'maxCount' => config\model\Alias::DEFAULT_MAX_COUNT,
        'hashMethod' => config\model\Alias::DEFAULT_HASH_METHOD,
        'cacheHashLength' => config\model\Alias::DEFAULT_HASH_LENGTH,
    ];
    /**
     * @var AliasConfigFactory
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
    public $formatterConfig = [];
    /**
     * @var FormatterConfigFactory
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
        $this->formatterFactory = FormatterConfigFactory::build((array) $this->formatterConfig);
        $this->aliasFactory = AliasConfigFactory::build($this->defaultAlias);
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

    /**
     * @param string|null $format
     * @return config\formatter\File
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function getFormatterConfig(?string $format): config\formatter\File
    {
        return $this->formatterFactory->getConfig($format);
    }


    /* OTHER methods */

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
     * @param array $config
     * @return array|string
     * @throws \RuntimeException
     * @throws \ErrorException
     * @throws \Exception
     */
    public function getFileUrl(IFile $file, array $config = [])
    {
        if ($file->getId() === null || !$this->cacheFile($file, $config)) {
            return $this->getNotFoundUrl($file);
        }

        return [$this->getAssetPath($file, $config)];
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
     * @param IFile $file
     * @param array $config
     * @return bool
     * @internal param string $fileType
     */
    public function cacheFile(IFile $file, array $config = []): bool
    {
        try {
            $config['filePath'] = $config['filePath'] ?? $this->getFilePath($file);
            $processor = (new formatter\Factory($this))->build($file, $this->uploadFS, $config);
            $fileCache = new CacheComponent($file, $this->cacheFS, $this->getAssetPath($file, $config));

            return $fileCache->cache($processor);
        } catch (\Exception $e) {
            // Do nothing, just catch exception and keep executing.
            return false;
        }
    }


    /* FILE PATH builders */

    /**
     * Returns path for caching files.
     * @param IFile $file
     * @param array $config
     * @return string
     * @throws \RuntimeException
     * @throws \ErrorException
     * @throws \Exception
     */
    public function getAssetPath(IFile $file, array $config = []): string
    {
        $formatConfig = $this->getFormatterConfig($config['format'] ?? null);
        $aliasConfig = $this->getAliasConfig($file->getModelAlias());

        return implode('/', [
            $this->cacheBasePath,
            Type::$folderPrefix[$file->getType()] . $formatConfig->name, // e.g.: 'file', 'image_normal'
            mb_substr($file->getHash(), 0, $aliasConfig->cacheHashLength),
            $file->getId() . '_' . $file->getFullName(),
        ]);
    }

    /**
     * Returns file path in contentFS or empty string.
     * @param IFile $file
     * @return string|null
     * @throws \ErrorException
     */
    public function getFilePath(IFile $file): ?string
    {
        if ($file->getId() === null) {
            return null;
        }

        return $this->getFileDirectory($file) . DIRECTORY_SEPARATOR . $this->getFileName($file);
    }

    /**
     * @param IFile $file
     * @return string
     */
    public function getFileName(IFile $file): string
    {
        return $file->getId() . ($file->getExtension() !== null ? '.' . $file->getExtension() : '');
    }

    /**
     * Returns target directory for uploaded file
     * @param IFile $file
     * @return string
     * @throws \ErrorException
     */
    public function getFileDirectory(IFile $file): string
    {
        $aliasConfig = $this->getAliasConfig($file->getModelAlias());

        return $aliasConfig->directory
            . DIRECTORY_SEPARATOR
            . $this->getDirectoryHash($aliasConfig->name, (string) $file->getId());
    }

    /**
     * @param string $name
     * @param string $value E.g. directory name
     * @return string
     * @throws \ErrorException
     */
    protected function getDirectoryHash(string $name, string $value): ?string
    {
        $aliasConfig = $this->getAliasConfig($name);

        return $aliasConfig->cacheHashLength > 0
            ? mb_substr(hash($aliasConfig->hashMethod, $value), 0, $aliasConfig->cacheHashLength)
            : null;
    }
}
