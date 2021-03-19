<?php

namespace fredyns\attachment\migrations;

use yii\db\Migration;
use yii\db\Schema;

class m150127_040544_create_attachment_table extends Migration
{
    use \fredyns\attachment\ModuleTrait;

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($this->getModule()->tableName, [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . ' not null',
            'model' => Schema::TYPE_STRING . ' not null',
            'item_id' => Schema::TYPE_INTEGER . ' not null',
            'hash' => Schema::TYPE_STRING . ' not null',
            'size' => Schema::TYPE_INTEGER . ' not null',
            'type' => Schema::TYPE_STRING . ' not null',
            'mime' => Schema::TYPE_STRING . ' not null',
            'uploaded_at' => Schema::TYPE_INTEGER . ' not null',
            'uploaded_by' => Schema::TYPE_INTEGER . ' not null',
        ], $tableOptions);

        $this->createIndex('file_model', $this->getModule()->tableName, ['model', 'item_id']);
    }

    public function down()
    {
        $this->dropTable($this->getModule()->tableName);
    }
}
