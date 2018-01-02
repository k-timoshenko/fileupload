<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\saver;

use tkanstantsin\yii2fileupload\model\File;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class FileSaverHelper updates already uploaded files and binds it to model
 */
class FileListSaver extends Model
{
    /**
     * Owner model
     * @var ActiveRecord
     */
    public $model;
    /**
     * @var string
     */
    public $alias;
    /**
     * @var File[]
     */
    public $fileArray;

    /**
     * File config for file manager
     * @var array
     */
    public $aliasConfig;

    /**
     * FileSaverHelper constructor.
     * @param ActiveRecord $model
     * @param array $aliasConfig
     */
    public function __construct(ActiveRecord $model, array $aliasConfig)
    {
        $this->model = $model;
        $this->aliasConfig = $aliasConfig;

        parent::__construct();
    }

    /**
     * Initialize helper
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        $this->alias = $this->aliasConfig['alias'] ?? null;
        $this->fileArray = $this->getFiles();
        parent::init();
    }

    /**
     * @param $data
     * @param string|null $formName
     * @throws \yii\base\InvalidConfigException
     */
    public function load($data, $formName = null): void
    {
        $formName = $formName ?? $this->alias;
        $data = ArrayHelper::getValue($data, $formName, []);
        $data = \is_array($data) ? $data : [];

        $idArray = array_keys($data);
        $this->fileArray = $this->getFilesByIds($idArray);

        $priority = 1;
        foreach ($this->fileArray as $file) {
            // load data
            // data. NOTE: may be filename too.
            $fileData = $data[$file->id] ?? [
                    'is_deleted' => true,
                ];
            $file->attributes = array_intersect_key($fileData, array_fill_keys(['is_deleted'], null));

            if (!$file->is_confirmed && !$file->is_deleted) {
                $file->is_confirmed = true;
            }
            $file->priority = $file->isActual() ? $priority++ : null;
        }

        $this->fileArray = ArrayHelper::index($this->sortFileArray($this->fileArray, $idArray), 'id');
    }

    public function rules(): array
    {
        return [
            ['model', function ($attribute) {
                if ($this->$attribute->isNewRecord || $this->$attribute->id === null) {
                    $this->$attribute->addError($attribute, 'Parent model must be saved.');
                }
            }],
            [
                'fileArray',
                function ($attribute) {
                    // max count
                    $fileArray = array_filter($this->fileArray, function (File $file) {
                        return $file->isActual();
                    });
                    if (\count($fileArray) > $this->aliasConfig['maxCount']) {
                        $this->addError($attribute, 'Max file count is ' . $this->aliasConfig['maxCount']);
                    }

                    if (!Model::validateMultiple($fileArray)) {
                        foreach ($this->fileArray as $file) {
                            $this->addErrors($file->getErrors());
                        }
                    }
                },
            ],
        ];
    }

    /**
     * @param bool $validate
     * @return bool
     * @throws \yii\base\InvalidParamException
     */
    public function save($validate = true): bool
    {
        if ($validate && !$this->validate()) {
            return false;
        }

        $saved = true;
        foreach ($this->fileArray as $file) {
            $file->parent_model_id = $file->parent_model_id ?? $this->model->id;
            $saved = $saved && $file->save(true, [
                    'parent_model_id', 'name', 'is_confirmed', 'is_deleted', 'priority',
                ]);
        }

        return $saved;
    }

    /**
     * Currently actual model files
     * @return \tkanstantsin\yii2fileupload\model\File[]
     * @internal param array|null $idArray
     * @throws \yii\base\InvalidConfigException
     */
    protected function getFiles(): array
    {
        if ($this->model->isNewRecord) {
            return [];
        }

        return File::find()
            ->byModel($this->alias, $this->model->id)
            ->isActual()
            ->orderByPriority()
            ->all();
    }

    /**
     * Searches for files bound for current model instance or for model alias
     * but parent_model_id is null.
     * @param array $idArray
     * @return File[]
     * @throws \yii\base\InvalidConfigException
     */
    protected function getFilesByIds(array $idArray): array
    {
        if (\count($idArray) === 0) {
            return [];
        }

        return File::find()
            ->byModel($this->alias, $this->model->id, true)
            ->byIds($idArray)
            ->isDeleted(false)
            ->orderByPriority()
            ->all();
    }

    /**
     * Sort file array as in input data
     * @param File[] $oldFileArray
     * @param array $idArray - input id order
     * @return File[]
     */
    protected function sortFileArray(array $oldFileArray, array $idArray): array
    {
        $newFileArray = [];
        $oldFileArray = ArrayHelper::index($oldFileArray, 'id');
        foreach ($idArray as $id) {
            $file = ArrayHelper::remove($oldFileArray, $id);
            if ($file !== null) {
                $newFileArray[] = $file;
            }
        }

        return array_merge($newFileArray, $oldFileArray);
    }
}
