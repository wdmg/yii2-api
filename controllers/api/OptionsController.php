<?php

namespace wdmg\api\controllers\api;

use yii\base\Object;
use yii\web\NotFoundHttpException;
use wdmg\api\controllers\RestController;
use Yii;

class OptionsController extends RestController
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->modelClass = new Object();
        if(class_exists('\wdmg\options\models\Options') && isset(Yii::$app->modules['options']))
            $this->modelClass = 'wdmg\api\models\api\OptionsAPI';
        else
            throw new NotFoundHttpException('Requested API not found.');

        parent::init();
    }
}

?>