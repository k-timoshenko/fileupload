<?php

namespace tkanstantsin\fileupload\saver;

use League\Flysystem\FilesystemInterface;
use tkanstantsin\fileupload\FileManager;
use tkanstantsin\fileupload\formatter\Factory as FormatterFactory;
use tkanstantsin\fileupload\model\IFile;

/**
 * Class FileDuplicator makes full copy of existed file (only content)
 */
class Replicator
{
    /**
     * @var FileManager
     */
    protected $fileManager;
    /**
     * @var IFile
     */
    protected $originalFile;
    /**
     * @var FilesystemInterface
     */
    protected $originalFS;
    /**
     * @var IFile
     */
    protected $replicaFile;
    /**
     * @var FilesystemInterface
     */
    protected $replicaFS;

    /**
     * FileSaver constructor.
     * @param FileManager $fileManager
     * @param IFile $originalFile
     * @param FilesystemInterface $originalFS
     * @param IFile $replicaFile
     * @param FilesystemInterface $replicaFS
     */
    public function __construct(FileManager $fileManager,
                                IFile $originalFile,
                                FilesystemInterface $originalFS,
                                IFile $replicaFile,
                                FilesystemInterface $replicaFS)
    {
        $this->fileManager = $fileManager;
        $this->originalFile = $originalFile;
        $this->originalFS = $originalFS;
        $this->replicaFile = $replicaFile;
        $this->replicaFS = $replicaFS;
    }

    /**
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \ErrorException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function run(): bool
    {
        $originalFileFormatter = $this->fileManager->buildFormatter(
            $this->replicaFile,
            FormatterFactory::FILE_ORIGINAL
        );

        $replicaAlias = $this->fileManager->getAliasConfig($this->replicaFile->getModelAlias());
        $newPath = $replicaAlias->getFilePath($this->replicaFile);
        $saver = new Saver($this->replicaFile, $this->originalFS, $newPath);

        return $saver->save($originalFileFormatter);
    }
}
