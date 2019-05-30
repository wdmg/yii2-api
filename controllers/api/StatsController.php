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
        $this->modelClass = new Object();
        if(class_exists('\wdmg\stats\models\Visitors') && isset(Yii::$app->modules['stats']))
            $this->modelClass = 'wdmg\api\models\api\StatsAPI';
        else
            throw new NotFoundHttpException('Requested API not found.');

        parent::init();
    }
}

?>