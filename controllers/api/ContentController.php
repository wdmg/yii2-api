<?php

namespace wdmg\api\controllers\api;

use yii\base\BaseObject;
use yii\web\NotFoundHttpException;
use wdmg\api\controllers\RestController;
use Yii;

class ContentController extends RestController
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $module_id = 'content';
        if($this->module->module)
            $module_id = $this->module->module->id . '/' . $module_id;

        $this->modelClass = new BaseObject();
        if(class_exists('\wdmg\content\models\Content') && Yii::$app->hasModule($module_id))
            $this->modelClass = 'wdmg\api\models\api\ContentAPI';
        else
            throw new NotFoundHttpException('Requested API not found.');

        parent::init();
    }
}

?>