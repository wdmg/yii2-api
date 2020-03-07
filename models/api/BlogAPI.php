<?php

namespace wdmg\api\models\api;

use Yii;
use wdmg\blog\models\Posts;

class BlogAPI extends Posts
{
    private $allowedFields = [
        'id',
        'name',
        'alias',
        'image',
        'excerpt',
        'content',
        'categories',
        'tags',
        'title',
        'description',
        'keywords',
        'url',
        'status',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];

    public function fields()
    {
        if (!$fields = parent::fields())
            $fields = parent::attributes();

        foreach ($fields as $key => $field) {
            if (!in_array($field, $this->allowedFields))
                unset($fields[$key]);
        }

        return $fields;
    }
}