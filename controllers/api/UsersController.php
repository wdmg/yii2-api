<?php

namespace wdmg\api\controllers\api;

use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\ContentNegotiator;
use wdmg\api\controllers\RestController;
use Yii;
use yii\web\Response;

class UsersController extends RestController
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->modelClass = 'wdmg\api\models\api\UsersAPI';
        parent::init();
    }
}

?>