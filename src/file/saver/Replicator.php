<?php

namespace common\components\file\savers;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use tkanstantsin\fileupload\FileManager;
use tkanstantsin\fileupload\model\IFile;
use tkanstantsin\yii2fileupload\model\File;

/**
 * Class FileDuplicator makes full copy of existed file (copy in DB and FS)
 * @todo: corretly implement it and test.
 */
class Replicator
{
    /**
     * @var FileManager
     */
    protected $fileManager;
    /**
     * @var Filesystem
     */
    protected $filesystem;
    /**
     * @var IFile[]
     */
    protected $files;
    /**
     * @var int
     */
    protected $newOwnerId;

    /**
     * FileSaver constructor.
     * @param FileManager $fileManager
     * @param FilesystemInterface $filesystem
     * @param IFile[] $files
     * @param int $newOwnerId
     * @throws \ErrorException
     */
    public function __construct(FileManager $fileManager, FilesystemInterface $filesystem, array $files, int $newOwnerId)
    {
        $this->fileManager = $fileManager;
        $this->filesystem = $filesystem;
        $this->newOwnerId = $newOwnerId;
        $this->files = (array) $files;


        foreach ($this->files as $file) {
            if (!($file instanceof IFile)) {
                throw new \ErrorException(sprintf('File must be instance of `%s`.', IFile::class));
            }
        }
    }

    /**
     * @return bool
     */
    public function replicate(): bool
    {
        $saved = true;
        foreach ($this->files as $file) {
            $saved = $saved && $this->replicateFile($file);
        }

        return $saved;
    }

    /**
     * @param \tkanstantsin\yii2fileupload\model\File $file
     * @return bool
     * @throws \ErrorException
     */
    protected function replicateFile(File $file): bool
    {
        $fileAttributes = $file->attributes;
        unset($fileAttributes['id']);

        $newFile = new File($fileAttributes);
        $newFile->parent_model_id = $this->newOwnerId;
        $newFile->is_confirmed = true;

        $saved = $newFile->save();

        $aliasConfig = $this->fileManager->getAliasConfig($file->getModelAlias());

        $saved = $saved && $this->filesystem->copy($aliasConfig->getFilePath($file), $aliasConfig->getFilePath($newFile));

        return $saved;
    }
}
