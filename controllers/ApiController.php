<?php

namespace wdmg\api\controllers;

use Yii;
use yii\base\DynamicModel;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\data\ArrayDataProvider;

/**
 * ApiController implements the CRUD actions for API model.
 */
class ApiController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['GET', 'POST'],
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'roles' => ['admin'],
                        'allow' => true
                    ],
                ],
            ],
        ];

        // If auth manager not configured use default access control
        if(!Yii::$app->authManager) {
            $behaviors['access'] = [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'roles' => ['@'],
                        'allow' => true
                    ],
                ]
            ];
        }

        return $behaviors;
    }

    /**
     * Lists of all API`s.
     * @return mixed
     */
    public function actionIndex()
    {

        // Get allowed models
        if (isset(Yii::$app->params['api.allowedApiModels'])) {
            if (is_array(Yii::$app->params['api.allowedApiModels']))
                $allowedModels = Yii::$app->params['api.allowedApiModels'];
            else
                $allowedModels = unserialize(Yii::$app->params['api.allowedApiModels']);
        } else {
            $allowedModels = Yii::$app->controller->module->allowedApiModels;
        }

        $i = 1;
        $publicAllowedModels = [];
        foreach ($allowedModels['public'] as $modelClass => $status) {
            $publicAllowedModels[$i] = [
                'id' => $i,
                'class' => $modelClass,
                'status' => $status,
            ];
            $i++;
        }

        $i = 1;
        $privateAllowedModels = [];
        foreach ($allowedModels['private'] as $modelClass => $status) {
            $privateAllowedModels[$i] = [
                'id' => $i,
                'class' => $modelClass,
                'status' => $status,
            ];
            $i++;
        }

        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->get('change') == "status") {
                if (Yii::$app->request->post('id', null)) {
                    $id = Yii::$app->request->post('id');
                    $mode = Yii::$app->request->post('mode', 'public');
                    $status = Yii::$app->request->post('value', 0);

                    if ($mode == 'public')
                        $publicAllowedModels[$id]['status'] = boolval($status);
                    elseif ($mode == 'private')
                        $privateAllowedModels[$id]['status'] = boolval($status);

                    $allowedApiModels = [];
                    foreach ($publicAllowedModels as $allowedModel) {
                        $allowedApiModels['public'][$allowedModel['class']] = boolval($allowedModel['status']);
                    }
                    foreach ($privateAllowedModels as $allowedModel) {
                        $allowedApiModels['private'][$allowedModel['class']] = boolval($allowedModel['status']);
                    }
                    var_dump(Yii::$app->options->set('api.allowedApiModels', $allowedApiModels, 'array', null, true, false));
                }
            }
        }

        $publicDataProvider = new ArrayDataProvider([
            'allModels' => $publicAllowedModels,
            'sort' => [
                'attributes' => ['id', 'class', 'status'],
            ],
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        $privateDataProvider = new ArrayDataProvider([
            'allModels' => $privateAllowedModels,
            'sort' => [
                'attributes' => ['id', 'class', 'status'],
            ],
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('index', [
            'public' => $publicDataProvider,
            'private' => $privateDataProvider,
        ]);
    }
}
