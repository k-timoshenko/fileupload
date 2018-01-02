<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\stub\formatter;

/**
 * Class FalseFormatterStub always return false value instead of content
 */
class FalseFormatterStub extends AbstractFormatterStub
{
    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return false;
    }
}