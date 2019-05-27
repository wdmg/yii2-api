<?php

namespace wdmg\api\controllers;

use Yii;
use wdmg\api\models\API;
use wdmg\api\models\APISearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

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
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['get'],
                ],
            ],
        ];
    }

    /**
     * Lists all models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new APISearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Testing API.
     * @return mixed
     */
    public function actionTest()
    {

        $model = new \yii\base\DynamicModel(['action', 'request', 'method', 'accept']);

        $apiActions = [
            '/api/users' => 'Users API',
            '/api/options' => 'Options API'
        ];

        $allowedMethods = [
            'get' => 'GET',
            'post' => 'POST',
            'head' => 'HEAD',
            'patch' => 'PATCH',
            'put' => 'PUT',
            'delete' => 'DELETE',
            'options' => 'OPTIONS'
        ];

        $acceptResponses = [
            'json' => 'application/json',
            'xml' => 'application/xml'
        ];

        $model->addRule(['action'], 'in', ['range' => array_keys($apiActions)]);

        $model->addRule(['method'], 'in', ['range' => array_keys($allowedMethods)]);
        $model->addRule(['method'], 'default', ['value' => 'post']);

        $model->addRule(['accept'], 'in', ['range' => array_keys($acceptResponses)]);
        $model->addRule(['accept'], 'default', ['value' => 'json']);

        $model->addRule(['request'], 'string', ['min' => 3, 'max' => 255]);

        $model->addRule(['action', 'method', 'accept'], 'required');

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // do what you want
        }

        return $this->render('test', [
            'model' => $model,
            'apiActions' => $apiActions,
            'requestMethods' => $allowedMethods,
            'acceptResponses' => $acceptResponses,
        ]);
    }

    /**
     * Finds the model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = API::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app/modules/api', 'The requested page does not exist.'));
    }
}
