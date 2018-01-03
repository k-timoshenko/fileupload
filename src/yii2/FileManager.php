<?php
declare(strict_types=1);

namespace tkanstantsin\yii2fileupload;

use tkanstantsin\fileupload\FileManager as BaseFileManager;
use tkanstantsin\fileupload\formatter\Factory as FormatterFactory;
use tkanstantsin\fileupload\model\IFile;
use tkanstantsin\fileupload\model\Type;
use tkanstantsin\fileupload\model\Type as FileType;
use tkanstantsin\yii2fileupload\model\File;
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
     * Caches file and returns url to it. Return 404 image or link if fails
     * without exception.
     * @param IFile|null $file
     * @param string $format
     * @param array $formatterConfig
     * @return string|null
     * @throws \InvalidArgumentException
     * @throws \yii\base\InvalidParamException
     * @throws \RuntimeException
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \tkanstantsin\fileupload\config\InvalidConfigException
     */
    public function getFileUrl(?IFile $file, string $format, array $formatterConfig = []): ?string
    {
        if ($file === null) {
            return null;
        }

        if ($file->getId() !== null) { // null if file is not saved yet
            $path = $this->manager->getFilePath($file, $format, $formatterConfig);
            if ($path !== null) {
                return implode(DIRECTORY_SEPARATOR, [
                    $this->cacheBasePath,
                    $path,
                ]);
            }
        }

        return $this->getNotFoundUrl($file);
    }

    /**
     * Choose 404 url
     * @param IFile|null $file
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function getNotFoundUrl(?IFile $file): string
    {
        switch ($file->getType()) {
            case Type::IMAGE:
                return Url::to($this->imageNotFoundUrl);
            case Type::FILE:
            default:
                return Url::to($this->fileNotFoundUrl);
        }
    }

    /**
     * Generates file info for response to jQueryFileUpload widget.
     * @param File $file
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \yii\base\InvalidParamException
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \tkanstantsin\fileupload\config\InvalidConfigException
     */
    public function getJQueryUploadFileData(File $file): array
    {
        return [
            // file model
            'id' => $file->getId(),
            'alias' => $file->getModelAlias(),
            'model_id' => $file->getModelId(),
            // file info
            'name' => $file->getFullName(),
            'size' => $file->getSize(),
            'is_deleted' => (integer) $file->is_deleted,
            'is_confirmed' => (integer) $file->is_confirmed,
            // urls
            // path to full image or file itself.
            'url' => Url::to($this->manager->getFilePath($file, FormatterFactory::FILE_ORIGINAL)),
            // path to image thumbnail or file icon.
            'preview_url' => $file->getType() === FileType::IMAGE
                ? Url::to($this->manager->getFilePath($file, FormatterFactory::IMAGE_DEFAULT_FORMAT))
                : null,
            'icon' => $this->manager->getIcon($file->getExtension()),
        ];
    }

    /**
     * Generates file info for multiple files for jQuery File Upload widget.
     * @param IFile[] $fileArray
     * @return array
     */
    public function getJQueryFileDataArray(array $fileArray): array
    {
        return array_map([$this, 'getFileData'], array_values($fileArray));
    }
}
