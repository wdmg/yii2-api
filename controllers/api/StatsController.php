<?php

namespace wdmg\api\controllers\api;

use yii\base\Object;
use yii\web\NotFoundHttpException;
use wdmg\api\controllers\RestController;
use Yii;

class StatsController extends RestController
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $module_id = 'stats';
        if($this->module->module)
            $module_id = $this->module->module->id . '/' . $module_id;

        $this->modelClass = new Object();
        if(class_exists('\wdmg\stats\models\Visitors') && Yii::$app->hasModule($module_id))
            $this->modelClass = 'wdmg\api\models\api\StatsAPI';
        else
            throw new NotFoundHttpException('Requested API not found.');

        parent::init();
    }
}

?>