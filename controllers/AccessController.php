<?php

namespace wdmg\api\controllers;

use Yii;
use wdmg\api\models\API;
use wdmg\api\models\APISearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * AccessController implements the CRUD actions for API model.
 */
class AccessController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['GET', 'POST'],
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
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
                'class' => AccessControl::class,
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
     * Lists all models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->get('change') == "status") {
                if (Yii::$app->request->post('id', null)) {
                    $id = Yii::$app->request->post('id');
                    $status = Yii::$app->request->post('value', 0);
                    $model = $this->findModel(intval($id));
                    if ($model) {
                        $model->status = intval($status);
                        if ($model->update())
                            return true;
                        else
                            return false;
                    }
                }
            } else if (Yii::$app->request->get('change') == "access-token") {
                if (Yii::$app->request->post('id', null)) {
                    $id = Yii::$app->request->post('id');
                    $model = $this->findModel(intval($id));
                    if ($model) {
                        $model->access_token = $model->generateAccessToken();
                        if ($model->update())
                            return true;
                        else
                            return false;
                    }
                }
            }
        }

        $searchModel = new APISearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Creates a new client.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new API();
        if ($model->load(Yii::$app->request->post()) && $model->save())
            return $this->redirect(['index']);

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing client.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model && Yii::$app->request->isAjax) {
            if (Yii::$app->request->get('change') == "access-token") {
                $model->access_token = $model->generateAccessToken();
                if ($model->update())
                    return true;
                else
                    return false;
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->save())
            return $this->redirect(['index']);

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing client.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Displays a single client.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->renderAjax('view', [
            'model' => $model
        ]);
    }

    /**
     * Testing API.
     * @return mixed
     */
    public function actionTest()
    {

        $accessToken = '';
        if (Yii::$app->request->get('access-token'))
            $accessToken = Yii::$app->request->get('access-token');

        $model = new \wdmg\base\models\DynamicModel(['action', 'request', 'method', 'accept']);

        $apiActions = [
            '/api/users' => 'Users API',
            '/api/options' => 'Options API',
            '/api/redirects' => 'Redirects API',

            '/api/pages' => 'Pages API',
            '/api/news' => 'News API',
            '/api/blog' => 'Blog API',
            '/api/mailer' => 'Mailer API',

            '/api/media' => 'Media API',

            '/api/tasks' => 'Tasks API',
            '/api/tickets' => 'Tickets API',

            '/api/content' => 'Content API',
            '/api/stats' => 'Stats API',

            '/api/search' => 'Search API',
            '/api/live-search' => 'LiveSearch API',

            '/api/newsletters' => 'Newsletters API',

            '/api/subscribers' => 'Subscribers API',
            '/api/subscribers-list' => 'Subscribers list API',
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
            'accessToken' => $accessToken,
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
