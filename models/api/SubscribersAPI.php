<?php

namespace wdmg\api\models\api;

use Yii;
use wdmg\subscribers\models\Subscribers;

class SubscribersAPI extends Subscribers
{
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['unique_token']);
        return $fields;
    }
}
