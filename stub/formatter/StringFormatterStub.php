<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\stub\formatter;

/**
 * Class StringFormatterStub always return own name as value instead of content
 */
class StringFormatterStub extends AbstractFormatterStub
{
    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return static::class;
    }
}