<?php

namespace tkanstantsin\fileupload\saver;

use League\Flysystem\FilesystemInterface;
use tkanstantsin\fileupload\config\Alias;
use tkanstantsin\fileupload\FileManager;
use tkanstantsin\fileupload\model\Type;
use tkanstantsin\yii2fileupload\model\File;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * Class FileUpload
 *
 * @todo: create Yii2 independent Uploader and/or split existed uploader.
 */
class Uploader extends File
{
    /**
     * @var FileManager
     */
    public $fileManager;
    /**
     * @var UploadedFile
     */
    public $uploadedFile;
    /**
     * @var Alias
     */
    private $aliasConfig;
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * FileUpload constructor.
     * @param FileManager $fileManager
     * @param UploadedFile $uploadedFile
     * @param array $aliasConfig
     * @param FilesystemInterface $filesystem
     * @param array $config
     */
    public function __construct(FileManager $fileManager, UploadedFile $uploadedFile, Alias $aliasConfig, FilesystemInterface $filesystem, array $config = [])
    {
        $this->fileManager = $fileManager;
        $this->uploadedFile = $uploadedFile;
        $this->aliasConfig = $aliasConfig;
        $this->filesystem = $filesystem;

        parent::__construct($config);
    }

    public function init(): void
    {
        parent::init();

        $this->parent_model = $this->aliasConfig->name;
        $this->name = pathinfo($this->uploadedFile->name, PATHINFO_FILENAME);
        $this->extension = $this->uploadedFile->extension;
        $this->size = (int) $this->uploadedFile->size;
        $this->mime_type = $this->uploadedFile->type;
        $this->type_id = Type::getByMimeType((string) $this->mime_type);
        $this->hash = \is_resource($this->uploadedFile->tempName)
            ? hash('md5', stream_get_contents($this->uploadedFile->tempName))
            : hash_file('md5', $this->uploadedFile->tempName);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return ArrayHelper::merge(parent::rules(), [
            [['uploadedFile', 'parent_model'], 'required'],
            [['uploadedFile'], 'file', 'maxSize' => $this->aliasConfig->maxSize],
        ]);
    }

    /**
     * Saves file
     * @return bool
     * @throws \League\Flysystem\FileExistsException
     * @throws \InvalidArgumentException
     * @throws \ErrorException
     */
    public function upload(): bool
    {
        $saved = $this->save();

        // ignore saving error
        $this->uploadFile($this->aliasConfig->getFileDirectory($this), $this->aliasConfig->getFileName($this));

        return $saved;
    }

    /**
     * Copy file into FileSaver::$filesystem
     * @param string $dirPath
     * @param string $fileName
     * @return bool
     * @throws \League\Flysystem\FileExistsException
     * @throws \InvalidArgumentException
     */
    public function uploadFile(string $dirPath, string $fileName): bool
    {
        try {
            if (!$this->filesystem->createDir($dirPath)) {
                return false;
            }

            $targetFilePath = $dirPath . DIRECTORY_SEPARATOR . $fileName;

            if ($this->filesystem->has($targetFilePath)) {
                $this->addError('', "File by path `{$targetFilePath}` already exists.");

                return false;
            }

            return $this->filesystem->writeStream($targetFilePath, $this->getFileContent());
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return resource|null
     */
    private function getFileContent()
    {
        $resource = \is_resource($this->uploadedFile->tempName)
            ? $this->uploadedFile->tempName
            : fopen($this->uploadedFile->tempName, 'rb');

        return $resource ?: null;
    }
}

