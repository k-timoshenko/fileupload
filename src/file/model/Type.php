<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 9/29/16
 * Time: 3:09 PM
 */

namespace tkanstantsin\fileupload\model;

/**
 * Class FileType
 */
class Type
{
    public const FILE = 1;
    public const IMAGE = 2;

    public static $folderPrefix = [
        self::FILE => 'file',
        self::IMAGE => 'image',
    ];

    /**
     * @return array
     */
    public static function all(): array
    {
        return [
            self::FILE => 'File',
            self::IMAGE => 'Image',
        ];
    }

    /**
     * @param int $id
     * @return string
     */
    public static function get(int $id): string
    {
        return static::all()[$id] ?? 'Not set';
    }

    /**
     * Normalizes file type string into correct array
     * @param string $fileType
     * @return array
     */
    public static function normalize(string $fileType): array
    {
        $typeArray = explode('_', $fileType);

        $normalized = [
            'original' => $fileType,
            'type' => $typeArray[0] ?? self::FILE,
        ];

        if ($normalized['type'] === self::IMAGE) {
            $normalized['width'] = $typeArray[0] ?? null;
            $normalized['height'] = $typeArray[1] ?? null;
        }

        return $normalized;
    }

    /**
     * Returns file type by its mime type.
     * @param string $mimeType
     * @return int
     */
    public static function getByMimeType(string $mimeType): int
    {
        if (mb_strpos($mimeType, 'image') === 0) {
            return static::IMAGE;
        }

        return static::FILE;
    }
}