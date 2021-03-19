<?php

namespace fredyns\attachment;

use fredyns\attachment\models\File;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\i18n\PhpMessageSource;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'fredyns\attachment\controllers';

    public $defaultRoute = 'file';

    public $storePath = '@app/uploads/store';

    public $tempPath = '@app/uploads/temp';

    public $rules = [];

    public $tableName = 'attachment';

    public function init()
    {
        parent::init();

        if (empty($this->storePath) || empty($this->tempPath)) {
            throw new InvalidConfigException('Setup {storePath} and {tempPath} in module properties');
        }

        $this->rules = ArrayHelper::merge(['maxFiles' => 3], $this->rules);
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        Yii::$app->i18n->translations['fredyns/*'] = [
            'class' => PhpMessageSource::className(),
            'sourceLanguage' => 'en',
            'basePath' => '@vendor/fredyns/yii2-attachment/src/messages',
            'fileMap' => [
                'fredyns/attachment' => 'attachment.php'
            ],
        ];
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('fredyns/' . $category, $message, $params, $language);
    }

    public function getStorePath()
    {
        return Yii::getAlias($this->storePath);
    }

    public function getTempPath()
    {
        return Yii::getAlias($this->tempPath);
    }

    /**
     * @param $fileHash
     * @return string
     */
    public function getFilesDirPath($fileHash)
    {
        return $this->getStorePath() . DIRECTORY_SEPARATOR . $this->getSubDirs($fileHash);
    }

    public function getSubDirs($fileHash, $depth = 3)
    {
        $depth = min($depth, 9);
        $path = '';

        for ($i = 0; $i < $depth; $i++) {
            $folder = substr($fileHash, $i * 3, 2);
            $path .= $folder;
            if ($i != $depth - 1) $path .= DIRECTORY_SEPARATOR;
        }

        return $path;
    }

    public function getUserDirPath()
    {
        Yii::$app->session->open();

        $userDirPath = $this->getTempPath() . DIRECTORY_SEPARATOR . Yii::$app->session->id;
        FileHelper::createDirectory($userDirPath);

        Yii::$app->session->close();

        return $userDirPath . DIRECTORY_SEPARATOR;
    }

    public function getShortClass($obj)
    {
        $className = get_class($obj);
        if (preg_match('@\\\\([\w]+)$@', $className, $matches)) {
            $className = $matches[1];
        }
        return $className;
    }

    /**
     * @param $filePath string
     * @param $owner
     * @return bool|File
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function attachFile($filePath, $owner)
    {
        if (empty($owner->id)) {
            throw new InvalidConfigException('Parent model must have ID when you attaching a file');
        }

        $fileHash = md5(microtime(true) . $filePath);
        $fileType = pathinfo($filePath, PATHINFO_EXTENSION);
        $newFileName = "$fileHash.$fileType";
        $fileDirPath = $this->getFilesDirPath($fileHash);
        $newFilePath = $fileDirPath . DIRECTORY_SEPARATOR . $newFileName;

        $stream = fopen($filePath, 'r+');
        Yii::$app->awss3Fs->write("attachment" . DIRECTORY_SEPARATOR . $newFilePath, $stream);
        fclose($stream);

        $file = new File();
        $file->name = pathinfo($filePath, PATHINFO_FILENAME);
        $file->model = $this->getShortClass($owner);
        $file->item_id = $owner->id;
        $file->hash = $fileHash;
        $file->size = filesize($filePath);
        $file->type = $fileType;
        $file->mime = FileHelper::getMimeType($filePath);
        $file->uploaded_by = (Yii::$app->user->isGuest) ? 0 : Yii::$app->user->identity->id;
        $file->uploaded_at = time();

        if ($file->save()) {
            unlink($filePath);
            return $file;
        } else {
            return false;
        }
    }

    public function detachFile($id)
    {
        /* @var $file File */
        $file = File::findOne(['id' => $id]);
        if (empty($file)) {
            return false;
        }

        $filePath = $this->getFilesDirPath($file->hash) . DIRECTORY_SEPARATOR . $file->hash . '.' . $file->type;
        $exists = Yii::$app->awss3Fs->has("attachment" . DIRECTORY_SEPARATOR . $filePath);
        $file->delete();
        if ($exists) {
            Yii::$app->awss3Fs->delete("attachment" . DIRECTORY_SEPARATOR . $filePath);
        }

        return true;

    }
}
