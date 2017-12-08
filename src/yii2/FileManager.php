<?php
/**
 * Created by Konstantin Timoshenko
 * Email: t.kanstantsin@gmail.com
 * Date: 4/6/16 at 2:13 AM
 */

namespace tkanstantsin\yii2fileupload;

use tkanstantsin\fileupload\model\IFile;
use tkanstantsin\fileupload\model\Type;
use tkanstantsin\fileupload\processor\FormatHelper;
use tkanstantsin\yii2fileupload\model\File;
use tkanstantsin\fileupload\FileManager as BaseFileManager;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\Url;

/**
 * Class FileComponent
 */
class FileManager extends Component
{
    /**
     * @var BaseFileManager
     */
    public $manager;

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        $this->manager['uploadFS'] = \Yii::$app->{$this->manager['uploadFS']};
        $this->manager['webFS'] = \Yii::$app->{$this->manager['webFS']};

        $this->manager = \Yii::createObject($this->manager);
        if (!($this->manager instanceof BaseFileManager)) {
            throw new InvalidConfigException(\Yii::t('yii2fileupload', 'Invalid file manager config'));
        }

        parent::init();
    }

    // TODO: proxy all methods to the real filemanager.

    /**
     * Generates file info for response to jQueryFileUpload widget.
     * @param File $file
     * @return array
     * @throws \yii\base\InvalidParamException
     * @throws \ErrorException
     */
    public function getFileData(File $file): array
    {
        return [
            // file model
            'id' => $file->getId(),
            'alias' => $file->getModelAlias(),
            'model_id' => $file->getModelId(),
            // file info
            'name' => $file->getFullName(),
            'size' => $file->getSize(),
            'is_deleted' => (integer) $file->is_deleted,
            'is_confirmed' => (integer) $file->is_confirmed,
            // urls
            // path to full image or file itself.
            'url' => Url::to($this->manager->getFileUrl($file, ['format' => FormatHelper::FILE_ORIGINAL])),
            // path to image thumbnail or file icon.
            'preview_url' => $file->getType() === Type::IMAGE
                ? Url::to($this->manager->getFileUrl($file, ['format' => FormatHelper::IMAGE_UPLOAD_PREVIEW]))
                : null,
            'icon' => $this->manager->getFileIcon($file),
        ];
    }

    /**
     * Generates file info for multiple files for jQuery File Upload widget.
     * @param IFile[] $fileArray
     * @return array
     */
    public function getFileDataArray(array $fileArray): array
    {
        return array_map([$this, 'getFileData'], array_values($fileArray));
    }
}
