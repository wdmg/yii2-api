<?php

namespace wdmg\api\models\api;

use Yii;
use wdmg\subscribers\models\SubscribersList;

class SubscribersListAPI extends SubscribersList
{
    public function fields()
    {
        $fields = parent::fields();
        return $fields;
    }
}
