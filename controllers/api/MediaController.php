<?php

namespace wdmg\api\controllers\api;

use yii\base\BaseObject;
use yii\web\NotFoundHttpException;
use wdmg\api\controllers\RestController;
use Yii;

class MediaController extends RestController
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $module_id = 'media';
        if($this->module->module)
            $module_id = $this->module->module->id . '/' . $module_id;

        $this->modelClass = new BaseObject();
        if(class_exists('\wdmg\media\models\Media') && Yii::$app->hasModule($module_id))
            $this->modelClass = 'wdmg\api\models\api\MediaAPI';
        else
            throw new NotFoundHttpException('Requested API not found.');

        parent::init();
    }
}

?>