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
            if (!in_array($key, $this->allowedFields))
                unset($fields[$key]);
        }

        return $fields;
    }

    public function extraFields()
    {
        return [
            'tags' => function() {
                return $this->getTags();
            },
            'categories' => function() {
                return $this->getCategories();
            },
            /*'created_by' => function() {
                return $this->getCreatedBy();
            },
            'updated_by' => function() {
                return $this->getUpdatedBy();
            },*/
        ];
    }
}