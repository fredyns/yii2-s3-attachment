<?php

namespace fredyns\attachment\models;

use fredyns\attachment\ModuleTrait;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * This is the model class for table "attachment".
 *
 * @property integer $id
 * @property string $name
 * @property string $model
 * @property integer $item_id
 * @property string $hash
 * @property integer $size
 * @property string $type
 * @property string $mime
 * @property integer $uploaded_at
 * @property integer $uploaded_by
 */
class File extends ActiveRecord
{
    use ModuleTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Yii::$app->getModule('attachment')->tableName;
    }

    /**
     * @inheritDoc
     */
    public function fields()
    {
        return [
            'url'
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'model', 'item_id', 'uploaded_by', 'hash', 'size', 'type', 'mime'], 'required'],
            [['item_id', 'uploaded_by', 'size'], 'integer'],
            [['name', 'model', 'hash', 'type', 'mime'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'model' => 'Model',
            'item_id' => 'Item ID',
            'hash' => 'Hash',
            'size' => 'Size',
            'type' => 'Type',
            'mime' => 'Mime',
            'uploaded_by' => 'Uploaded By',
            'uploaded_by' => 'Uploaded At',
        ];
    }

    public function getUrl()
    {
        return Url::to(['/attachment/file/download', 'id' => $this->id]);
    }

    public function getPath()
    {
        return $this->getModule()->getFilesDirPath($this->hash) . DIRECTORY_SEPARATOR . $this->hash . '.' . $this->type;
    }
}
