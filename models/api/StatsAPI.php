<?php

namespace wdmg\api\models\api;

use Yii;
use wdmg\stats\models\Visitors;

class StatsAPI extends Visitors
{
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['session'], $fields['params']);
        return $fields;
    }
}
