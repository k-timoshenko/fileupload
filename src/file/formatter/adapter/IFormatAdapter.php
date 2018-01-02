<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\formatter\adapter;

use tkanstantsin\fileupload\model\IFile;

/**
 * Interface IFormatAdapter
 */
interface IFormatAdapter
{
    /**
     * Applies filters or something to content and return it
     *
     * @param IFile $file
     * @param       $content
     *
     * @return mixed
     */
    public function exec(IFile $file, $content);
}