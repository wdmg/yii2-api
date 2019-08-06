<?php

namespace wdmg\api\models\api;

use Yii;
use wdmg\news\models\News;

class NewsAPI extends News
{
    public function fields()
    {
        $fields = parent::fields();
        return $fields;
    }
}
