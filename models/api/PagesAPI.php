<?php

namespace wdmg\api\models\api;

use Yii;
use wdmg\pages\models\Pages;

class PagesAPI extends Pages
{
    private $allowedFields = [
        'id',
        'parent_id',
        'source_id',
        'name',
        'alias',
        'content',
        'title',
        'description',
        'keywords',
        'url',
        'locale',
        'status'
    ];

    public function fields()
    {
        if (!$fields = parent::fields())
            $fields = parent::attributes();

        foreach ($fields as $key => $field) {
            if (!in_array($field, $this->allowedFields))
                unset($fields[$key]);
        }

        $fields['url'] = function ($model) {
            return $model->getUrl(true);
        };

        return $fields;
    }

    public function extraFields()
    {
        return [
            'created' => function() {
                if ($created = $this->getCreatedBy()->one()) {
                    return [
                        'id' => $created->id,
                        'username' => $created->username,
                        'datetime' => $this->created_at,
                    ];
                }
                return null;
            },
            'updated' => function() {
                if ($updated = $this->getUpdatedBy()->one()) {
                    return [
                        'id' => $updated->id,
                        'username' => $updated->username,
                        'datetime' => $this->updated_at,
                    ];
                }
                return null;
            },
        ];
    }
}