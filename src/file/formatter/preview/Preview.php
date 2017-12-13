<?php
/**
 * Created by Konstantin Timoshenko
 * Email: t.kanstantsin@gmail.com
 * Date: 2/2/16 at 11:47 PM
 */

namespace tkanstantsin\fileupload\formatter\preview;

use tkanstantsin\fileupload\model\IFile;
use tkanstantsin\fileupload\model\Type;
use yii\helpers\Url;

/**
 * Class PreviewCreator
 * @todo: refactor.
 */
class Preview
{
    public const DEFAULT_PREVIEW = 'default';

    /**
     * @var array
     */
    public static $iconPreviewArray = [
        'file' => ['txt', 'doc',],
    ];

    /**
     * @var array
     */
    public static $availableExtensionArray = [
        'jpg', 'jpeg', 'png',
    ];

    /**
     * @param IFile $file
     * @return mixed|string
     * @throws \yii\base\InvalidParamException
     */
    public static function create(IFile $file)
    {
        return static::isPreviewAvailable($file)
            // TODO: move into component.
            ? Url::to([
                '/file/get',
                'id' => $file->getId(),
                'updatedAt' => $file->getUpdatedAt(),
                'fileType' => Type::IMAGE,
                'fileName' => $file->getFullName(),
            ])
            : static::getIcon($file);
    }

    /**
     * @param IFile $file
     * @return mixed
     */
    public static function getIcon(IFile $file)
    {
        // return icon
        foreach (static::$iconPreviewArray as $preview => $extensionArray) {
            if (\in_array($file->getExtension(), $extensionArray, true)) {
                return static::getPreviewIcon($preview);
            }
        }

        return static::getPreviewIcon(self::DEFAULT_PREVIEW);
    }

    public static function getPreviewIcon($preview)
    {
        return $preview;
    }

    /**
     * @param IFile $file
     * @return bool
     */
    protected static function isPreviewAvailable(IFile $file): bool
    {
        return \in_array($file->getExtension(), static::$availableExtensionArray, true);
    }
}
