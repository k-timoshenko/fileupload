<?php

namespace tkanstantsin\fileupload;

use tkanstantsin\fileupload\config\Alias;
use tkanstantsin\fileupload\formatter\File as FileFormatter;
use tkanstantsin\fileupload\model\IFile;

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

    public function __construct(IFile $file, Alias $alias, FileFormatter $formatter)
    {
        $this->file = $file;
        $this->alias = $alias;
        $this->formatter = $formatter;

        $this->init();
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
    }
}