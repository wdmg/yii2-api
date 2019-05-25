<?php

namespace wdmg\api\models;

use Yii;

/**
 * This is the model class for table "{{%api}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $user_ip
 * @property string $entity_id
 * @property int $target_id
 * @property int $is_like
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Users $user
 */

class API extends \yii\db\ActiveRecord
{
    const API_STATUS_DISABLED = 0;
    const API_STATUS_ACTIVE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%api}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [['user_id', 'status'], 'integer'],
            [['user_id', 'user_ip', 'access_token', 'status'], 'required'],
            [['status'], 'default', 'value' => self::API_STATUS_ACTIVE],
            [['user_ip'], 'string', 'max' => 39],
            [['access_token'], 'string', 'max' => 32],
            [['created_at', 'updated_at'], 'safe'],
        ];

        if(class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users'])) {
            $rules[] = [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \wdmg\users\models\Users::className(), 'targetAttribute' => ['user_id' => 'id']];
        }
        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/modules/api', 'ID'),
            'user_id' => Yii::t('app/modules/api', 'User ID'),
            'user_ip' => Yii::t('app/modules/api', 'User IP'),
            'access_token' => Yii::t('app/modules/api', 'Access token'),
            'status' => Yii::t('app/modules/api', 'Status'),
            'created_at' => Yii::t('app/modules/api', 'Created At'),
            'updated_at' => Yii::t('app/modules/api', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        if(class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users']))
            return $this->hasOne(\wdmg\users\models\Users::className(), ['id' => 'user_id']);
        else
            return null;
    }
}
