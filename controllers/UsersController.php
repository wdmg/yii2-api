<?php

namespace wdmg\api\controllers;

use yii\rest\ActiveController;

class UsersController extends ActiveController
{
    public $modelClass = 'wdmg\users\models\Users';

    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }
}

?>