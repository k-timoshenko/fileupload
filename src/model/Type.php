<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\model;

/**
 * Class FileType
 */
class Type
{
    public const FILE = 1;
    public const IMAGE = 2;
    public const DOC = 3;
    public const VIDEO = 4;
    public const AUDIO = 5;
    public const ARCHIVE = 5;

    public static $folderPrefix = [
        self::FILE => 'file',
        self::IMAGE => 'image',
        self::DOC => 'doc',
        self::VIDEO => 'video',
        self::AUDIO => 'audio',
        self::ARCHIVE => 'archive',
    ];

    /**
     * @return array
     */
    public static function all(): array
    {
        return [
            self::FILE => 'File',
            self::IMAGE => 'Image',
            self::DOC => 'Document',
            self::VIDEO => 'Video',
            self::AUDIO => 'Audio',
            self::ARCHIVE => 'Archive',
        ];
    }

    /**
     * @param int $id
     * @return string
     */
    public static function get(int $id): ?string
    {
        return static::all()[$id] ?? null;
    }

    /**
     * Returns file type by its mime type.
     * @param string|null $mimeType
     * @return int
     */
    public static function getByMimeType(?string $mimeType): int
    {
        switch (explode('/', (string) $mimeType)[0] ?? null) {
            case 'image':
                return static::IMAGE;
            case 'audio':
                return static::AUDIO;
            case 'video':
                return static::VIDEO;
            default:
                return static::FILE;
        }
    }
}