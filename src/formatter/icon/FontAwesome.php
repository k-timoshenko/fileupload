<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\formatter\icon;

/**
 * Class FontAwesome
 *
 * @see http://fontawesome.io
 */
class FontAwesome
{
    public const PREFIX = 'fa';

    public const ICON_SET = [
        // docs
        IconGenerator::ICON_TEXT => 'fa-file-text-o',
        IconGenerator::ICON_DOC => 'fa-file-word-o',
        IconGenerator::ICON_EXCEL => 'fa-file-excel-o',
        IconGenerator::ICON_POWER_POINT => 'fa-file-powerpoint-o',
        IconGenerator::ICON_PDF => 'fa-file-pdf-o',
        // images
        IconGenerator::ICON_IMAGE => 'fa-file-image-o',
        // archives
        IconGenerator::ICON_ARCHIVE => 'fa-file-archive-o',
        // multimedia
        IconGenerator::ICON_AUDIO => 'fa-file-audio-o',
        IconGenerator::ICON_VIDEO => 'fa-file-video-o',
    ];
}