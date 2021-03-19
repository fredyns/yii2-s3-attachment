Yii2 attachment
================
[![Latest Stable Version](https://poser.pugx.org/fredyns/yii2-attachment/v/stable)](https://packagist.org/packages/fredyns/yii2-attachment)
[![License](https://poser.pugx.org/fredyns/yii2-attachment/license)](https://packagist.org/packages/fredyns/yii2-attachment)
[![Total Downloads](https://poser.pugx.org/fredyns/yii2-attachment/downloads)](https://packagist.org/packages/fredyns/yii2-attachment)

Extension for file uploading and attaching to the models

Demo
----
You can see the demo on the [krajee](http://plugins.krajee.com/file-input/demo) website

Installation
------------

1. The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

	Either run
	
	```
	composer require fredyns/yii2-attachment "@dev"
	```
	
	or add
	
	```
	"fredyns/yii2-attachment": "@dev"
	```
	
	to the require section of your `composer.json` file.

2.  Add module to `config/main.php`
	
	```php
	'modules' => [
		...
		'attachment' => [
			'class' => fredyns\attachment\Module::class,
			'tempPath' => '@app/uploads/temp',
			'storePath' => '@app/uploads/store',
			'rules' => [ // Rules according to the FileValidator
			    'maxFiles' => 10, // Allow to upload maximum 3 files, default to 3
				'mimeTypes' => 'image/*', // Only png images
				'maxSize' => 1024 * 1024 // 1 MB
			],
			'tableName' => '{{%attachment}}' // Optional, default to 'attachment'
		]
		...
	]
	```

3. Apply migrations

	```php
    'controllerMap' => [
		...
		'migrate' => [
			'class' => 'yii\console\controllers\MigrateController',
			'migrationNamespaces' => [
				'fredyns\attachment\migrations',
			],
		],
		...
    ],
	```

	```
	php yii migrate/up
	```

4. Attach behavior to your model (be sure that your model has "id" property)
	
	```php
	public function behaviors()
	{
		return [
			...
			'fileBehavior' => [
				'class' => \fredyns\attachment\behaviors\FileBehavior::class
			]
			...
		];
	}
	```
	
5. Make sure that you specified `maxFiles` in module rules and `maxFileCount` on `AttachmentInput` to the number that you want

Usage
-----

1. In the `form.php` of your model add file input
	
	```php
	<?= \fredyns\attachment\components\AttachmentInput::widget([
		'id' => 'file-input', // Optional
		'model' => $model,
		'options' => [ // Options of the Kartik's FileInput widget
			'multiple' => true, // If you want to allow multiple upload, default to false
		],
		'pluginOptions' => [ // Plugin options of the Kartik's FileInput widget 
			'maxFileCount' => 10 // Client max files
		]
	]) ?>
	```

2. Use widget to show all attachment of the model in the `view.php`
	
	```php
	<?= \fredyns\attachment\components\AttachmentTable::widget(['model' => $model]) ?>
	```

3. (Deprecated) Add onclick action to your submit button that uploads all files before submitting form
	
	```php
	<?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', [
		'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary',
		'onclick' => "$('#file-input').fileinput('upload');"
	]) ?>
	```
	
4. You can get all attached files by calling ```$model->files```, for example:

	```php
	foreach ($model->files as $file) {
        echo $file->path;
    }
    ```
