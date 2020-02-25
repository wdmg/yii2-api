<?php

namespace wdmg\api\controllers\api;

use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use wdmg\api\controllers\RestController;
use Yii;

class SearchController extends RestController
{

    public $reservedParams = ['query', 'q'];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $module_id = 'search';
        if($this->module->module)
            $module_id = $this->module->module->id . '/' . $module_id;

        $this->modelClass = new BaseObject();
        if(class_exists('\wdmg\search\models\Search') && Yii::$app->hasModule($module_id))
            $this->modelClass = 'wdmg\api\models\api\SearchAPI';
        else
            throw new NotFoundHttpException('Requested API not found.');

        parent::init();
    }

    public function actions() {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }

    public function prepareDataProvider() {

        $params = \Yii::$app->request->queryParams;
        $query = $params['query'];

        $model = new $this->modelClass;
        $results = $model->search($query);

        $fileds = $model->fields();
        foreach ($results as $indx => $result) {
            foreach ($result as $key => $value) {
                if (!in_array($key, $fileds))
                    unset($results[$indx][$key]);
            }
        }

        return $results;
    }
}

?>