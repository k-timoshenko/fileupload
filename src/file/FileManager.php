<?php
/**
 * Created by Konstantin Timoshenko
 * Email: t.kanstantsin@gmail.com
 * Date: 4/6/16 at 2:13 AM
 */

namespace tkanstantsin\fileupload;

use League\Flysystem\Filesystem;
use tkanstantsin\fileupload\model\IFile;
use tkanstantsin\fileupload\model\Type;

/**
 * Class FileComponent
 */
class FileManager
{
    public const DEFAULT_MAX_SIZE = 5 * 1000 * 1000; // 5 MB
    public const DEFAULT_MAX_COUNT = 5;
    public const DEFAULT_HASH_LENGTH = 3;
    public const DEFAULT_HASH_METHOD = 'crc32'; // not crypto-strong, but fast (as needed)

    public const EXT_ICON_DEFAULT = 'fa-file-o';

    /**
     * Default list of icons for each extension.
     * It may be overridden with [[extIconArray]] property.
     * @var array
     */
    public static $defaultExtIconArray = [
        // docs
        'txt' => 'fa-file-text-o',
        'doc[x]?|odt' => 'fa-file-word-o',
        'xls[xb]?|ods' => 'fa-file-excel-o',
        'ptt[x]?|odp' => 'fa-file-powerpoint-o',
        'pdf' => 'fa-file-pdf-o',
        // images
        'jpe?g|png|gif' => 'fa-file-image-o',
        // archives
        'zip|rar|7zip' => 'fa-file-archive-o',
        // multimedia
        'mp3' => 'fa-file-audio-o',
        'mp4' => 'fa-file-video-o',
    ];

    /**
     * @var array
     * @todo: add accept type.
     * @example
     * ```
     * // Simple:
     * 'alias name' => 'class name',
     * // Customizable:
     * 'alias name' => [
     *  'alias' => 'alias name', // additional alias name value. Used for
     *   better access by class name.
     *  'class' => '',
     *  'directory' => '',
     *  'maxSize' => '',
     *  'maxCount' => '',
     *  'hashLength' => '',
     *  'hashMethod' => '',
     * ]
     * ```
     */
    public $aliases;
    /**
     * @var integer
     */
    public $maxSize = self::DEFAULT_MAX_SIZE;
    /**
     * @var integer
     */
    public $maxCount = self::DEFAULT_MAX_COUNT;
    /**
     * @var int
     */
    public $hashLength = self::DEFAULT_HASH_LENGTH;
    /**
     * @var int
     */
    public $hashMethod = self::DEFAULT_HASH_METHOD;
    /**
     * Base path for uploading files
     * @var string
     */
    public $uploadBaseUrl;
    /**
     * Path to folder which would contain cached files.
     * @var string
     */
    public $cacheBasePath;
    /**
     * @var int
     */
    public $cacheHashLength = self::DEFAULT_HASH_LENGTH;

    /**
     * @var string|array
     */
    public $imageNotFoundUrl;
    /**
     * @var string|array
     */
    public $fileNotFoundUrl;

    /**
     * @var array
     */
    public $formattingConfig = [];
    /**
     * List of icons for each extension.
     * @var array
     */
    public $extIconArray = [];

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
     * @inheritdoc
     * @throws \ErrorException
     */
    public function init(): void
    {
        if (!\is_int($this->maxSize) || $this->maxSize <= 0) {
            // TODO: add i18n.
            throw new \ErrorException('Maximum SIZE of files MUST be integer and more than 0.');
        }
        if (!\is_int($this->maxCount) || $this->maxCount <= 0) {
            throw new \ErrorException('Maximum COUNT of files MUST be integer and more than 0.');
        }
        if (!\in_array($this->hashMethod, hash_algos(), true)) {
            throw new \ErrorException(sprintf('Hash method `%s` not found.1', $this->hashMethod));
        }
        if (empty($this->uploadBaseUrl)) {
            throw new \ErrorException('Base upload url must be defined.');
        }
        if (empty($this->cacheBasePath)) {
            throw new \ErrorException('Base path for cache must be defined.');
        }
        if ($this->formattingConfig === null || $this->fileNotFoundUrl === null) {
            throw new \ErrorException('URLs for not founded image and file must be defined.');
        }

        $this->extIconArray = array_merge(static::$defaultExtIconArray, $this->extIconArray);
        $this->formattingConfig = array_merge(processor\FormatHelper::$defaultImageSizesConfig, $this->formattingConfig);
        $this->prepareAliases();
    }

    /**
     * @param string $aliasName
     * @return array|string|null
     * @note there may be several aliases for one model, but method returns
     *     first
     */
    public function getAlias(string $aliasName)
    {
        return $this->aliases[$aliasName] ?? null;
    }

    /**
     * @param string $aliasName
     * @return array|string|null
     */
    public function getModelByAlias($aliasName): ?string
    {
        return $this->getAlias($aliasName)['class'] ?? null;
    }

    /**
     * @param $alias
     * @param $dirName
     * @return string
     */
    public function getDirectoryHash(string $alias, string $dirName): ?string
    {
        $config = $this->getAlias($alias);

        return $config['hashLength'] > 0
            ? substr(hash($config['hashMethod'], $dirName), 0, $config['hashLength'])
            : null;
    }

    /**
     * Returns target directory for uploaded file
     * @param IFile $file
     * @return string
     */
    public function getFileDirectory(IFile $file): string
    {
        $aliasConfig = $this->getAlias($file->getModelAlias());

        return $aliasConfig['directory']
            . DIRECTORY_SEPARATOR
            . $this->getDirectoryHash($aliasConfig['alias'], (string) $file->getId());
    }

    /**
     * @param IFile $file
     * @return string
     */
    public function getFileName(IFile $file): string
    {
        return $file->getId() . (\strlen($file->getExtension()) > 0 ? '.' . $file->getExtension() : '');
    }

    /**
     * Generates url for upload file with upload widget.
     * @param string $alias
     * @param $id
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function getUploadUrl(string $alias, $id): string
    {
        return implode(DIRECTORY_SEPARATOR, [$this->uploadBaseUrl, $alias, $id]);
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
     * Returns suitable icon by extension or default icon.
     * @param IFile $file
     * @return string
     */
    public function getFileIcon(IFile $file): string
    {
        foreach ($this->extIconArray as $key => $icon) {
            if ($key === $file->getExtension() || preg_match("/$key/", $file->getExtension())) {
                return $icon;
            }
        }

        return self::EXT_ICON_DEFAULT;
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
            $config['filePath'] = $this->getFilePath($file);
            $processor = (new processor\Factory($this))->build($file, $this->uploadFS, $config);
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

        $assetPath = implode('/', [
            $this->cacheBasePath,
            Type::$folderPrefix[$file->getType()] . $formatConfig->name, // e.g.: file, image_normal, image_purchase_preview.
            mb_substr($file->getHash(), 0, $this->hashLength),
            $file->getId() . '_' . $file->getFullName(),
        ]);

        return $assetPath;
    }

    /**
     * Returns file path in contentFS or empty string.
     * @param IFile $file
     * @return string|null
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
     * @return \tkanstantsin\fileupload\processor\FileConfig
     * @throws \ErrorException
     */
    public function getFormatConfig(int $type, string $format = null): processor\FileConfig
    {
        $formatConfig = $this->formattingConfig[$format] ?? null;

        if ($formatConfig === null) { // config not found
            return $this->getFormatConfig($type, processor\FormatHelper::getDefaultFormatByType($type));
        }
        if (\is_string($formatConfig)) { // for legacy configs
            return $this->getFormatConfig($type, $formatConfig);
        }

        return processor\FormatHelper::createFormatConfig($type, array_merge($formatConfig, [
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
        $defaultConfig = [
            'maxSize' => $this->maxSize,
            'maxCount' => $this->maxCount,
            'hashLength' => $this->hashLength,
            'hashMethod' => $this->hashMethod,
            'multiple' => $this->maxCount > 1,
        ];

        $aliasArray = [];
        foreach ($this->aliases as $alias => $config) {
            if (\is_string($config)) {
                $config = [
                    'class' => $config,
                ];
            }

            // set default options
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $config = array_replace($defaultConfig, $config);
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $config = array_merge($config, [
                'alias' => $alias,
                'directory' => $config['directory'] ?? $alias,
                'multiple' => $config['maxCount'] > 1,
            ]);

            if (!\in_array($config['hashMethod'], hash_algos(), true)) {
                throw new \ErrorException("Hash method `{$this->hashMethod}` not found.");
            }

            $aliasArray[$alias] = $config;
        }
        $this->aliases = $aliasArray;
    }
}
