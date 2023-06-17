<?php

use yii\db\Migration;

/**
 * Class m230618_010122_api
 */
class m230618_010122_api extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%api}}', 'expired_at', 'datetime');
        $this->createIndex('idx_api_expired','{{%api}}', ['expired_at'],false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_api_expired', '{{%api}}');
    }

}
