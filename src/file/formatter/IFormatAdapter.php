<?php

namespace tkanstantsin\fileupload\formatter;

/**
 * Interface IFormatAdapter
 */
interface IFormatAdapter
{
    /**
     * Applies filters or something to content and return it
     *
     * @param $content
     * @return mixed
     */
    public function exec($content);
}