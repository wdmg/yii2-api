<?php

namespace wdmg\api\models;

use Yii;
use wdmg\options\models\Options;

class OptionsAPI extends Options
{
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['autoload'], $fields['protected']);
        return $fields;
    }
}
