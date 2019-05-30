<?php

namespace wdmg\api\models\api;

use Yii;
use wdmg\tickets\models\Tickets;

class TicketsAPI extends Tickets
{
    public function fields()
    {
        $fields = parent::fields();
        return $fields;
    }
}
