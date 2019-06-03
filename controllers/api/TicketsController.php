<?php

namespace wdmg\api\controllers\api;

use yii\base\Object;
use yii\web\NotFoundHttpException;
use wdmg\api\controllers\RestController;
use Yii;

class TicketsController extends RestController
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $module_id = 'tickets';
        if($this->module->module)
            $module_id = $this->module->module->id . '/' . $module_id;

        $this->modelClass = new Object();
        if(class_exists('\wdmg\tickets\models\Tickets') && Yii::$app->hasModule($module_id))
            $this->modelClass = 'wdmg\api\models\api\TicketsAPI';
        else
            throw new NotFoundHttpException('Requested API not found.');

        parent::init();
    }
}

?>