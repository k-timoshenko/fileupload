<?php
declare(strict_types=1);

namespace tkanstantsin\yii2fileupload;

use tkanstantsin\fileupload\FileManager as BaseFileManager;
use tkanstantsin\fileupload\model\IFile;
use tkanstantsin\fileupload\model\Type as FileType;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Class FileComponent
 *
 * @todo: create repository only for yii2-filemanager widget
 * @todo: proxy all methods to the real filemanager.
 */
class FileManager extends Component
{
    /**
     * Will hide file formatting exception if true
     * @var bool
     */
    public $silentMode = true;

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
     * @var BaseFileManager
     */
    public $manager;

    /**
     * @inheritdoc
     * @throws \ErrorException
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        if ($this->uploadBaseUrl === null) {
            throw new \ErrorException('Base upload url must be defined.');
        }
        if ($this->cacheBasePath === null) {
            throw new \ErrorException('Base path for cache must be defined.');
        }
        if ($this->imageNotFoundUrl === null || $this->fileNotFoundUrl === null) {
            throw new \ErrorException('URLs for not founded image and file must be defined.');
        }

        $this->manager['contentFS'] = \Yii::$app->{$this->manager['contentFS']}->getFileSystem();
        $this->manager['cacheFS'] = \Yii::$app->{$this->manager['cacheFS']}->getFileSystem();

        $class = ArrayHelper::remove($this->manager, 'class', BaseFileManager::class);
        $this->manager = new $class($this->manager);
        if (!($this->manager instanceof BaseFileManager)) {
            throw new InvalidConfigException(\Yii::t('yii2fileupload', 'Invalid file manager config'));
        }

        parent::init();
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
     * @param IFile|null $file
     * @param string $format
     * @param array $formatterConfig
     * @return string
     * @throws \Exception
     * @throws \yii\base\InvalidParamException
     */
    public function getFileUrl(?IFile $file, string $format, array $formatterConfig = []): string
    {
        $path = $this->getFilePath($file, $format, $formatterConfig);
        if ($path !== null) {
            return $this->cacheBasePath . DIRECTORY_SEPARATOR . $path;
        }

        $fileTypeId = $file !== null ? $file->getType() : FileType::FILE;

        return $this->getNotFoundUrl($fileTypeId);
    }

    /**
     * Format file and return image link if failed
     * @param IFile|null $file
     * @param string $format
     * @param array $formatterConfig
     * @param string|null $notFoundUrl
     * @return string
     * @throws \Exception
     * @throws \yii\base\InvalidParamException
     */
    public function getImageUrl(?IFile $file, string $format, array $formatterConfig = [], string $notFoundUrl = null): string
    {
        $path = $this->getFilePath($file, $format, $formatterConfig);
        if ($path !== null) {
            return $this->cacheBasePath . DIRECTORY_SEPARATOR . $path;
        }

        return $notFoundUrl ?? $this->getNotFoundUrl(FileType::IMAGE);
    }

    /**
     * Choose 404 url
     * @param int $fileTypeId
     * @return string
     */
    public function getNotFoundUrl(int $fileTypeId): string
    {
        switch ($fileTypeId) {
            case FileType::IMAGE:
                return Url::to($this->imageNotFoundUrl);
            case FileType::FILE:
            default:
                return Url::to($this->fileNotFoundUrl);
        }
    }

    /**
     * Caches file and returns url to it. Return 404 image or link if fails
     * without exception.
     * @param IFile|null $file
     * @param string $format
     * @param array $formatterConfig
     * @return string|null
     * @throws \InvalidArgumentException
     * @throws \yii\base\InvalidParamException
     * @throws \RuntimeException
     * @throws \Exception
     */
    protected function getFilePath(?IFile $file, string $format, array $formatterConfig = []): ?string
    {
        if ($file === null || $file->getId() === null /*null if file is not saved yet*/) {
            return null;
        }

        try {
            return $this->manager->getFilePath($file, $format, $formatterConfig);
        } catch (\Exception $e) {
            if (!$this->silentMode) {
                throw $e;
            }
        }

        return null;
    }
}
