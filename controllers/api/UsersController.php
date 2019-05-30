<?php

namespace wdmg\api\controllers\api;

use yii\base\Object;
use yii\web\NotFoundHttpException;
use wdmg\api\controllers\RestController;
use Yii;

class UsersController extends RestController
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->modelClass = new Object();
        if(class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users']))
            $this->modelClass = 'wdmg\api\models\api\UsersAPI';
        else
            throw new NotFoundHttpException('Requested API not found.');

        parent::init();
    }
}

?>