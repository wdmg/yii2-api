<?php

namespace wdmg\api\models\api;

use Yii;
use wdmg\pages\models\Pages;

class PagesAPI extends Pages
{
    public function fields()
    {
        $fields = parent::fields();
        return $fields;
    }
}
