<?php

namespace wdmg\api\models\api;

use Yii;
use wdmg\search\models\Search;

class SearchAPI extends Search
{
    public function fields()
    {
        if (!$fields = parent::fields())
            $fields = parent::attributes();

        $filter = ["hash", "snippets", "created_at", "updated_at", "status"];
        foreach ($filter as $val) {
            if (($key = array_search($val, $fields)) !== false) {
                unset($fields[$key]);
            }
        }

        $fields = array_merge($fields, ["snippet"]);
        return array_values($fields);
    }
}
