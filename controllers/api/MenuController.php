<?php

namespace wdmg\api\controllers\api;

use Yii;
use yii\base\BaseObject;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use wdmg\api\controllers\RestController;

class MenuController extends RestController
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $module_id = 'menu';
        if ($this->module->module)
            $module_id = $this->module->module->id . '/' . $module_id;

        $this->modelClass = new BaseObject();
        if (class_exists('\wdmg\menu\models\Menu') && Yii::$app->hasModule($module_id))
            $this->modelClass = 'wdmg\api\models\api\MenuAPI';
        else
            throw new NotFoundHttpException('Requested API not found.');

        parent::init();
    }

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }

    public function prepareDataProvider()
    {
        $requestParams = Yii::$app->getRequest()->getBodyParams();
        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }

        /* @var $modelClass \yii\db\BaseActiveRecord */
        $modelClass = $this->modelClass;
        $query = $modelClass::find();
        $request = Yii::$app->request;

        if ($locale = $request->get('locale', null))
            $query->andWhere(['locale' => $locale]);

        if (!empty($filter)) {
            $query->andWhere($filter);
        }

        return Yii::createObject([
            'class' => ActiveDataProvider::className(),
            'query' => $query,
            'pagination' => [
                'params' => $requestParams,
            ],
            'sort' => [
                'params' => $requestParams,
            ],
        ]);

    }
}

?>