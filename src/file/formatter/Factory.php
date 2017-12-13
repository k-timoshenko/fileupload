<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 5.7.17
 * Time: 12.41
 */

namespace tkanstantsin\fileupload\formatter;

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
    public static $fileFormatterArray = [
        Type::FILE => File::class,
        Type::IMAGE => Image::class,
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
     * @return File
     * @throws \ErrorException
     */
    public function build(IFile $file, FilesystemInterface $filesystem, array $config): File
    {
        $class = static::$fileFormatterArray[$file->getType()] ?? null;

        if ($class === null) {
            throw new \ErrorException(sprintf('Formatter for type `%s` not found', $file->getType()));
        }

        $format = $config['format'] ?? null;
        unset($config['format']);
        $config['formatConfig'] = $this->fileManager->getFormatConfig($file->getType(), $format);

        // TODO: fix file type.
        return new $class($file, $filesystem, $config);
    }
}