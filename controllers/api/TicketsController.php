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
        $this->modelClass = new Object();
        if(class_exists('\wdmg\tickets\models\Tickets') && isset(Yii::$app->modules['tickets']))
            $this->modelClass = 'wdmg\api\models\api\TicketsAPI';
        else
            throw new NotFoundHttpException('Requested API not found.');

        parent::init();
    }
}

?>