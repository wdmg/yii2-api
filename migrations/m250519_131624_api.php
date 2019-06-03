<?php

use yii\db\Migration;

/**
 * Class m250519_131624_api
 */
class m250519_131624_api extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%api}}', [
            'id'=> $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'user_ip' => $this->string(39)->null(),
            'access_token' => $this->string(32)->notNull(),
            'status' => $this->tinyInteger(1)->null()->defaultValue(0),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'),
            'allowance' => $this->tinyInteger(3)->null()->defaultValue(0),
            'allowance_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex('idx_api_user','{{%api}}', ['user_id', 'user_ip', 'access_token'],true);
        $this->createIndex('idx_api_status','{{%api}}', ['status'],false);

        // If exist module `Users` set foreign key `user_id` to `users.id`
        if(class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users'])) {
            $userTable = \wdmg\users\models\Users::tableName();
            $this->addForeignKey(
                'fk_api_to_users',
                '{{%api}}',
                'user_id',
                $userTable,
                'id',
                'NO ACTION',
                'CASCADE'
            );
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_api_user', '{{%api}}');
        $this->dropIndex('idx_api_status', '{{%api}}');

        if(class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users'])) {
            $userTable = \wdmg\users\models\Users::tableName();
            if (!(Yii::$app->db->getTableSchema($userTable, true) === null)) {
                $this->dropForeignKey(
                    'fk_api_to_users',
                    '{{%api}}'
                );
            }
        }

        $this->truncateTable('{{%api}}');
        $this->dropTable('{{%api}}');
    }

}
