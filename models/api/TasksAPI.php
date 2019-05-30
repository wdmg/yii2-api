<?php

namespace wdmg\api\models\api;

use Yii;
use wdmg\tasks\models\Tasks;

class TasksAPI extends Tasks
{
    public function fields()
    {
        $fields = parent::fields();
        return $fields;
    }
}
