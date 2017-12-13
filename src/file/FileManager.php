<?php
/**
 * Created by Konstantin Timoshenko
 * Email: t.kanstantsin@gmail.com
 * Date: 4/6/16 at 2:13 AM
 */

namespace tkanstantsin\fileupload;

use League\Flysystem\Filesystem;
use tkanstantsin\fileupload\config\model\Alias;
use tkanstantsin\fileupload\formatter\icon\IconGenerator;
use tkanstantsin\fileupload\model\IFile;
use tkanstantsin\fileupload\model\Type;

/**
 * Class FileComponent
 */
class FileManager
{
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
     * @var array
     */
    public $formatConfig = [];

    /**
     * For uploading
     * @var Filesystem
     */
    public $uploadFS;
    /**
     * Web accessible FS
     * @var Filesystem
     */
    public $webFS;

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
        if (empty($this->uploadBaseUrl)) {
            throw new \ErrorException('Base upload url must be defined.');
        }
        if (empty($this->cacheBasePath)) {
            throw new \ErrorException('Base path for cache must be defined.');
        }
        if ($this->formatConfig === null || $this->fileNotFoundUrl === null) {
            throw new \ErrorException('URLs for not founded image and file must be defined.');
        }

        $this->iconGenerator = IconGenerator::build($this->iconSet);
        $this->formatConfig = array_merge(config\formatter\Factory::$defaultFormatterArray, $this->formatConfig);
        $this->prepareAliases();
    }

    /**
     * @param string $name
     * @return Alias
     * @throws \ErrorException
     */
    public function getAliasConfig(string $name): Alias
    {
        $aliasConfig = $this->aliasArray[$name] ?? null;
        if ($aliasConfig === null) {
            throw new \ErrorException(sprintf('Alias with key `%s` not defined.', $name));
        }

        return $aliasConfig;
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
     * @param string $name
     * @param string $value E.g. directory name
     * @return string
     * @throws \ErrorException
     */
    public function getDirectoryHash(string $name, string $value): ?string
    {
        $aliasConfig = $this->getAliasConfig($name);

        return $aliasConfig->cacheHashLength > 0
            ? mb_substr(hash($aliasConfig->hashMethod, $value), 0, $aliasConfig->cacheHashLength)
            : null;
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
     * @param IFile $file
     * @return string
     */
    public function getFileName(IFile $file): string
    {
        return $file->getId() . ($file->getExtension() !== null ? '.' . $file->getExtension() : '');
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
     * @param array $config
     * @return array|string
     * @throws \ErrorException
     */
    public function getFileUrl(IFile $file, array $config = [])
    {
        if ($file->getId() === null || !$this->cacheFile($file, $config)) {
            return $file->getType() === Type::IMAGE
                ? $this->imageNotFoundUrl
                : $this->fileNotFoundUrl;
        }

        return [$this->getAssetPath($file, $config)];
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
            $fileCache = new CacheComponent($file, $this->webFS, $this->getAssetPath($file, $config));

            return $fileCache->cache($processor);
        } catch (\Exception $e) {
            // Do nothing, just catch exception and keep executing.
            return false;
        }
    }

    /**
     * Returns path for caching files.
     * @param IFile $file
     * @param array $config
     * @return string
     * @throws \ErrorException
     */
    public function getAssetPath(IFile $file, array $config = []): string
    {
        $formatConfig = $this->getFormatConfig($file->getType(), $config['format'] ?? null);
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
     * @param int $type of file
     * @param string $format
     * @return config\formatter\File
     * @throws \ErrorException
     */
    public function getFormatConfig(int $type, string $format = null): config\formatter\File
    {
        $formatConfig = $this->formatConfig[$format] ?? null;

        if ($formatConfig === null) { // config not found
            return $this->getFormatConfig($type, config\formatter\Factory::getDefaultFormat($type));
        }
        if (\is_string($formatConfig)) { // for legacy configs
            return $this->getFormatConfig($type, $formatConfig);
        }

        return config\formatter\Factory::createFormatConfig($type, array_merge($formatConfig, [
            'name' => $format,
        ]));
    }

    /**
     * Prepares aliases config in proper view.
     * @throws \ErrorException
     * @todo: create Alias class.
     */
    protected function prepareAliases(): void
    {
        $factory = new config\model\Factory($this->defaultAlias);
        foreach ($this->aliasArray as $name => $config) {
            $name = (string) $name;
            $this->aliasArray[$name] = $factory->build($name, $config);
        }
    }
}
