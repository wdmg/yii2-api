<?php

namespace wdmg\api\controllers;

use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\ContentNegotiator;
use yii\filters\AccessControl;
use yii\filters\RateLimiter;
use yii\rest\ActiveController;
use Yii;
use wdmg\api\models\API;
use wdmg\users\models\Users;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use yii\web\ForbiddenHttpException;


class RestController extends ActiveController
{
    public $modelClass;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        \Yii::$app->user->enableSession = false;
    }

    /**
     * {@inheritdoc}
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => []
        ];

        // Get auth methods
        if (isset(Yii::$app->params['api.authMethods']))
            $authMethods = intval(Yii::$app->params['api.authMethods']);
        else
            $authMethods = Yii::$app->controller->module->authMethods;

        if ($authMethods['basicAuth'] == true)
            $behaviors['authenticator']['authMethods'][] = [
                'class' => HttpBasicAuth::className(),
                'auth' => [$this, 'auth']
            ];

        if ($authMethods['bearerAuth'] == true)
            $behaviors['authenticator']['authMethods'][] = HttpBearerAuth::className();

        if ($authMethods['paramAuth'] == true)
            $behaviors['authenticator']['authMethods'][] = QueryParamAuth::className();

        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/xml' => Response::FORMAT_XML,
            ],
        ];

        // Rate limit headers send?
        if (isset(Yii::$app->params['api.rateLimitHeaders']))
            $rateLimitHeaders = intval(Yii::$app->params['api.rateLimitHeaders']);
        else
            $rateLimitHeaders = Yii::$app->controller->module->rateLimitHeaders;

        if ($rateLimitHeaders == true)
            $behaviors['rateLimiter']['enableRateLimitHeaders'] = true;
        else
            $behaviors['rateLimiter']['enableRateLimitHeaders'] = false;

        // Get blocked IP`s
        if (isset(Yii::$app->params['api.blockedIp']))
            $blockedIp = intval(Yii::$app->params['api.blockedIp']);
        else
            $blockedIp = Yii::$app->controller->module->blockedIp;

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => function ($rule, $action) use ($blockedIp) {
                        if (Yii::$app->request->userIP) {
                            return (!in_array(Yii::$app->request->userIP, $blockedIp));
                        }
                        return true;
                    }
                ]
            ],
            'denyCallback' => function ($rule, $action) {
                throw new ForbiddenHttpException(Yii::t('app/modules/api', 'Access to API has blocked.'), -2);
            }
        ];
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        parent::checkAccess($action, $model, $params);
    }

    /**
     * BaseAuth
     */
    public function auth($username, $password)
    {
        $user = Users::findOne(['username' => $username]);
        if ($user->validatePassword($password)) {
            $client = Api::findIdentity($user->id);
            if ($client) {

                // Send access token in header
                if (isset(Yii::$app->params['api.sendAccessToken']))
                    $sendAccessToken = intval(Yii::$app->params['api.sendAccessToken']);
                else
                    $sendAccessToken = Yii::$app->controller->module->sendAccessToken;

                if($sendAccessToken)
                    Yii::$app->response->headers->set('X-Access-Token', $client->access_token);

                return $client;
            }
            return null;
        }
        return null;
    }
}

?>