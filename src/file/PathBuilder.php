<?php

namespace tkanstantsin\fileupload;

use tkanstantsin\fileupload\config\Alias;
use tkanstantsin\fileupload\formatter\File as FileFormatter;
use tkanstantsin\fileupload\model\IFile;
use tkanstantsin\fileupload\model\Type;

/**
 * Class PathBuilder
 */
class PathBuilder
{
    /**
     * @var IFile
     */
    public $file;
    /**
     * @var Alias
     */
    public $alias;
    /**
     * @var FileFormatter
     */
    public $formatter;

    /**
     * @var string
     */
    protected $uploadBaseUrl;
    /**
     * @var string
     */
    protected $cacheBasePath;

    public function __construct(IFile $file, Alias $alias, FileFormatter $formatter)
    {
        $this->file = $file;
        $this->alias = $alias;
        $this->formatter = $formatter;
    }

    public function init(): void
    {
        if (!($this->file instanceof IFile)) {
            throw new \RuntimeException(sprintf('File must be instance of %s.', IFile::class));
        }
        if (!($this->alias instanceof Alias)) {
            throw new \RuntimeException(sprintf('File must be instance of %s.', Alias::class));
        }
        if (!($this->formatter instanceof FileFormatter)) {
            throw new \RuntimeException(sprintf('File must be instance of %s.', FileFormatter::class));
        }
        if ($this->uploadBaseUrl === null) {
            throw new \RuntimeException('Base upload url must be defined.');
        }
        if ($this->cacheBasePath === null) {
            throw new \RuntimeException('Base path for cache must be defined.');
        }
    }

    /**
     * Returns path for caching files.
     * @param IFile $file
     * @param array $config
     * @return string
     * @throws \RuntimeException
     * @throws \ErrorException
     * @throws \Exception
     */
    public function getCachePath(): string
    {
        return implode('/', array_filter([
            $this->cacheBasePath,
            Type::$folderPrefix[$this->file->getType()] . '_' . $this->formatter->getName(),
            mb_substr($this->file->getHash(), 0, $this->alias->cacheHashLength),
            $this->alias->getAssetName($this->file),
        ]));
    }
}