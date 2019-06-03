<?php

namespace wdmg\api\controllers\api;

use yii\base\Object;
use yii\web\NotFoundHttpException;
use wdmg\api\controllers\RestController;
use Yii;

class TasksController extends RestController
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $module_id = 'tasks';
        if($this->module->module)
            $module_id = $this->module->module->id . '/' . $module_id;

        $this->modelClass = new Object();
        if(class_exists('\wdmg\tasks\models\Tasks') && Yii::$app->hasModule($module_id))
            $this->modelClass = 'wdmg\api\models\api\TasksAPI';
        else
            throw new NotFoundHttpException('Requested API not found.');

        parent::init();
    }
}

?>