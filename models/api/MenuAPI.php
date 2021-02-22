<?php

namespace wdmg\api\models\api;

use Yii;
use wdmg\menu\models\Menu;
use yii\helpers\ArrayHelper;

class MenuAPI extends Menu
{
    private $allowedFields = [
        'id',
        'name',
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

        $request = Yii::$app->request;
        $locale = $request->get('locale');
        $fields['items'] = function ($model) use ($locale) {
            if ($items = $model->getItems($model->id, $locale, true, false)) {
                foreach ($items as &$item) {
                    foreach ($item as $key => $data) {
                        if (!in_array($key, $this->allowedFields2)) {
                            unset($item[$key]);
                        } else {

                            if (isset($item['parent_id']))
                                $item['parent_id'] = intval($item['parent_id']);
                            else
                                $item['parent_id'] = null;

                            if (isset($item['only_auth']))
                                $item['only_auth'] = boolval($item['only_auth']);

                            if (isset($item['target_blank']))
                                $item['target_blank'] = boolval($item['target_blank']);

                        }
                    }
                }
            } else {
                return null;
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