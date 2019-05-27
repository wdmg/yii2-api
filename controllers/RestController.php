<?php

namespace wdmg\api\controllers;

use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\ContentNegotiator;
use yii\filters\AccessControl;
use yii\filters\RateLimiter;
use yii\rest\ActiveController;
use Yii;
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
            'class' => QueryParamAuth::className(),
        ];
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/xml' => Response::FORMAT_XML,
            ],
        ];
        $behaviors['rateLimiter']['enableRateLimitHeaders'] = true;
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => function ($rule, $action) {
                        $blockedIp = Yii::$app->controller->module->blockedIp;
                        if (Yii::$app->request->userIP) {
                            return (!in_array(Yii::$app->request->userIP, $blockedIp, true));
                        }
                        return true;
                    }
                ]
            ],
            'denyCallback' => function ($rule, $action) {
                throw new ForbiddenHttpException(Yii::t('app/modules/api', 'Access to API from your IP has blocked.'), -4);
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
}

?>