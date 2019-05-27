<?php

namespace wdmg\api\controllers;

use yii\rest\ActiveController;

class OptionsController extends ActiveController
{
    public $modelClass = 'wdmg\api\models\OptionsAPI';

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