<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\stub\formatter;

use League\Flysystem\FilesystemInterface;
use tkanstantsin\fileupload\formatter\File;
use tkanstantsin\fileupload\model\IFile;

/**
 * Class AbstractFormatterStub
 */
class AbstractFormatterStub extends File
{
    /**
     * FileProcessor constructor.
     * @param IFile $file
     * @param FilesystemInterface $filesystem
     * @param array $config
     * @throws \tkanstantsin\fileupload\config\InvalidConfigException
     * @throws \ReflectionException
     */
    public function __construct(IFile $file, FilesystemInterface $filesystem, array $config = [])
    {
        $config['name'] = $config['name'] ?? (new \ReflectionClass($this))->getShortName();
        $config['path'] = $config['path'] ?? $config['name'];

        parent::__construct($file, $filesystem, $config);
    }
}