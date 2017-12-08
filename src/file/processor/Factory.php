<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 5.7.17
 * Time: 12.41
 */

namespace tkanstantsin\fileupload\processor;

use League\Flysystem\FilesystemInterface;
use tkanstantsin\fileupload\FileManager;
use tkanstantsin\fileupload\model\IFile;
use tkanstantsin\fileupload\model\Type;

/**
 * Class Factory
 */
class Factory
{
    /**
     * @var array
     */
    public static $fileProcessors = [
        Type::FILE => FileProcessor::class,
        Type::IMAGE => ImageProcessor::class,
    ];

    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * Factory constructor.
     * @param FileManager $fileManager
     */
    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * @param IFile $file
     * @param FilesystemInterface $filesystem
     * @param array $config
     * @return FileProcessor
     * @throws \ErrorException
     */
    public function build(IFile $file, FilesystemInterface $filesystem, array $config): FileProcessor
    {
        $class = static::$fileProcessors[$file->getType()] ?? null;

        if ($class === null) {
            throw new \ErrorException('Processors for type `' . $file->getType() . '` not found');
        }

        $format = $config['format'] ?? null;
        unset($config['format']);
        $config['formatConfig'] = $this->fileManager->getFormatConfig($file->getType(), $format);

        // TODO: fix file type.
        return new $class($file, $filesystem, $config);
    }
}