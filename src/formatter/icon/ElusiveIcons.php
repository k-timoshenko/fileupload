<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\formatter\icon;

/**
 * Class ElusiveIcons
 *
 * @see http://elusiveicons.com
 */
class ElusiveIcons
{
    public const PREFIX = 'el';

    public const ICON_SET = [
        // docs
        IconGenerator::ICON_TEXT => 'el-icon-doc-text',
        IconGenerator::ICON_DOC => 'el-icon-file-word',
        IconGenerator::ICON_EXCEL => 'el-icon-file-excel',
        IconGenerator::ICON_POWER_POINT => 'el-icon-file-powerpoint',
        IconGenerator::ICON_PDF => 'el-icon-file-pdf',
        // images
        IconGenerator::ICON_IMAGE => 'el-icon-file-image',
        // archives
        IconGenerator::ICON_ARCHIVE => 'el-icon-file-archive',
        // multimedia
        IconGenerator::ICON_AUDIO => 'el-icon-file-audio',
        IconGenerator::ICON_VIDEO => 'el-icon-file-video',
    ];
}