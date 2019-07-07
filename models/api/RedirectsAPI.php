<?php

namespace wdmg\api\models\api;

use Yii;
use wdmg\redirects\models\Redirects;

class RedirectsAPI extends Redirects
{
    public function fields()
    {
        $fields = parent::fields();
        return $fields;
    }
}
