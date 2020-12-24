<?php

namespace wdmg\api\models\api;

use Yii;
use wdmg\menu\models\Menu;
use yii\helpers\ArrayHelper;

class MenuAPI extends Menu
{
    private $allowedFields = [
        'id',
        'title',
        'description',
        'alias',
        'status',
        'items'
    ];

    private $allowedFields2 = [
        'id',
        'parent_id',
        'name',
        'title',
        'source_url',
        'only_auth',
        'target_blank'
    ];

    public function fields()
    {
        if (!$fields = parent::fields())
            $fields = parent::attributes();

        foreach ($fields as $key => $field) {
            if (!in_array($field, $this->allowedFields))
                unset($fields[$key]);
        }

        $fields['items'] = function ($model) {
            $items = $model->getItems($model->id, true, false);

            foreach ($items as &$item) {
                foreach ($item as $key => $data) {
                    if (!in_array($key, $this->allowedFields2))
                        unset($item[$key]);
                }
            }

            return $items;
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