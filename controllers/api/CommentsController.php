<?php

namespace wdmg\api\controllers\api;

use yii\base\BaseObject;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use wdmg\api\controllers\RestController;
use Yii;

class CommentsController extends RestController
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $module_id = 'comments';
        if($this->module->module)
            $module_id = $this->module->module->id . '/' . $module_id;

        $this->modelClass = new BaseObject();
        if(class_exists('\wdmg\comments\models\Comments') && Yii::$app->hasModule($module_id))
            $this->modelClass = 'wdmg\api\models\api\CommentsAPI';
        else
            throw new NotFoundHttpException('Requested API not found.');

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function actions() {

        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function actionIndex($filter = null) {

        /* @var $model \yii\db\ActiveRecord */
        $model = new $this->modelClass;
        if ($filter = json_decode($filter,true)) {
            return new  ActiveDataProvider([
                'query' => $model::find()->where([
                    'or',
                    ['status' => $model::COMMENT_STATUS_PUBLISHED],
                    ['status' => $model::COMMENT_STATUS_DELETED]
                ])->andWhere($filter),
            ]);
        } else {
            return new ActiveDataProvider([
                'query' => $model::find()->where([
                    'or',
                    ['status' => $model::COMMENT_STATUS_PUBLISHED],
                    ['status' => $model::COMMENT_STATUS_DELETED]
                ]),
            ]);
        }
    }
}

?>