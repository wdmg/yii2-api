<?php

namespace wdmg\api\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\behaviors\TimeStampBehavior;
use yii\filters\RateLimitInterface;
use yii\base\NotSupportedException;
use yii\web\ForbiddenHttpException;
use yii\web\UnauthorizedHttpException;
use yii\helpers\ArrayHelper;

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
 * @property string $allowance
 * @property string $allowance_at
 *
 * @property Users $user
 */

class API extends ActiveRecord implements IdentityInterface, RateLimitInterface
{

    const API_CLIENT_STATUS_DISABLED = 0; // Access disabled for client
    const API_CLIENT_STATUS_ACTIVE = 1; // Access enabled for client

    public $module; // Base API module
    public $accessTokenExpire; // Access token expire time
    public $rateLimit; // Request`s limit per minute

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
    public function init()
    {
        parent::init();
        $this->module = Yii::$app->getModule('api');
        $this->accessTokenExpire = $this->module->accessTokenExpire;
        $this->rateLimit = $this->module->rateLimit;
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    self::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value' => function() {
                    return date("Y-m-d H:i:s");
                }
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [['user_id', 'status'], 'integer'],
            [['user_id', 'user_ip', 'status'], 'required'],
            [['status'], 'default', 'value' => self::API_CLIENT_STATUS_ACTIVE],
            [['user_ip'], 'string', 'max' => 39],
            [['access_token'], 'string', 'max' => 32],
            [['created_at', 'updated_at', 'allowance', 'allowance_at'], 'safe'],
        ];

        if(class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users'])) {
            $rules[] = [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \wdmg\users\models\Users::className(), 'targetAttribute' => ['user_id' => 'id']];
        }
        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if ($insert)
            $this->access_token = self::generateAccessToken();

        return parent::beforeSave($insert);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedIP()
    {
        return $this->user_ip();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        throw new NotSupportedException(Yii::t('app/modules/api', 'This method not allowed for API.'));
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        throw new NotSupportedException(Yii::t('app/modules/api', 'This method not allowed for API.'));
        return null;
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
            'allowance_at' => Yii::t('app/modules/api', 'Last access'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['user_id' => $id, 'status' => self::API_CLIENT_STATUS_ACTIVE]);
    }

    /**
     * Generates new access token
     * @return string
     */
    public function generateAccessToken()
    {
        $this->access_token = Yii::$app->security->generateRandomString();
        return $this->access_token;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {

        $client = static::findOne(['access_token' => $token, 'status' => self::API_CLIENT_STATUS_ACTIVE]);
        if (!$client) {
            return false;
        }

        // Check access for current user IP
        if (Yii::$app->request->userIP) {
            if (!in_array(Yii::$app->request->userIP, explode(",", $client->user_ip), true)) {
                throw new ForbiddenHttpException(Yii::t('app/modules/api', 'You do not have access to API from your IP.'), -3);
                return false;
            }
            if (in_array(Yii::$app->request->userIP, $client->module->blockedIp, true)) {
                throw new ForbiddenHttpException(Yii::t('app/modules/api', 'Access to API from your IP has blocked.'), -4);
                return false;
            }
        }

        // Get time to expire access token
        if (isset(Yii::$app->params['api.accessTokenExpire']))
            $expire = intval(Yii::$app->params['api.accessTokenExpire']);
        else
            $expire = $client->accessTokenExpire;

        // Check access token is expired
        if ($expire !== 0) { // of `0` - unlimited lifetime
            if ((strtotime($client->updated_at) + (string)$expire) < time()) {
                $old_access_token = $client->access_token;
                $client->access_token = $client->generateAccessToken();
                $new_access_token = $client->access_token;
                $client->update();

                throw new UnauthorizedHttpException('The access token expired and has been generated anew.', -1);
                /*Yii::$app->response->content = json_encode([
                    "access_token" => $old_access_token,
                    "refresh_token" => $new_access_token,
                    "expires" => $expire
                ]);*/
                return false;
            }
        }
        return $client;
    }

    public function getStatusModesList() {

        $items = [];
        return ArrayHelper::merge($items, [
            self::API_CLIENT_STATUS_ACTIVE => Yii::t('app/modules/api', 'Access enabled'),
            self::API_CLIENT_STATUS_DISABLED => Yii::t('app/modules/api', 'Access disabled'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getRateLimit($request, $action)
    {
        // Get rate limit per minute
        if (isset(Yii::$app->params['api.rateLimit']))
            $rateLimit = intval(Yii::$app->params['api.rateLimit']);
        else
            $rateLimit = $this->rateLimit;

        return [$rateLimit, 60];
    }

    /**
     * {@inheritdoc}
     */
    public function loadAllowance($request, $action)
    {
        return [intval($this->allowance), strtotime($this->allowance_at)];
    }

    /**
     * {@inheritdoc}
     */
    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        $this->updateAttributes([
            'allowance' => intval($allowance),
            'allowance_at' => date("Y-m-d H:i:s", $timestamp)
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsername()
    {
        return $this->user['username'];
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
