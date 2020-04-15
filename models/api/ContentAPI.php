<?php

namespace wdmg\api\models\api;

use Yii;
use wdmg\content\models\Blocks;

class ContentAPI extends Blocks
{
    private $allowedFields = [
        'id',
        'title',
        'description',
        'alias',
        'fields',
        'type',
        'content',
    ];

    public function fields()
    {
        if (!$fields = parent::fields())
            $fields = parent::attributes();

        foreach ($fields as $key => $field) {
            if (!in_array($key, $this->allowedFields))
                unset($fields[$key]);
        }

        $fields['fields'] = function ($model) {
            $fields = $model->getFields($model->fields, true);
            foreach ($fields as $key => $val) {
                if (!in_array($key, ['id', 'label', 'name', 'type', 'sort_order']))
                    unset($fields[$key]);
            }
            return $fields;
        };

        $fields['content'] = function ($model) {
            if ($model->type == $model::CONTENT_BLOCK_TYPE_ONCE)
                return $model->getBlockContent($this->id, true);
            elseif ($model->type == $model::CONTENT_BLOCK_TYPE_LIST)
                return $this->getListContent($model->id, true);
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
