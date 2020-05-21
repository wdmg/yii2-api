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
        'locale',
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

        $id = null;
        if (isset($_GET['id']))
            $id = intval($_GET['id']);

        if (is_null($id))
            $id = $this->id;

        $locale = null;
        if (isset($_GET['locale']))
            $locale = trim($_GET['locale']);

        if (is_null($locale))
            $locale = $this->locale;

        /*$fields['fields'] = function () use ($locale) {
            $fields = $this->getFields(null, $locale, true);
            foreach ($fields as $key => $val) {
                if (!in_array($key, ['id', 'label', 'locale', 'name', 'type', 'sort_order']))
                    unset($fields[$key]);
            }
            return $fields;
        };*/

        $fields['content'] = function () use ($id, $locale) {
            if ($this->type == $this::CONTENT_BLOCK_TYPE_ONCE)
                return $this->getBlockContent($id, $locale, true);
            elseif ($this->type == $this::CONTENT_BLOCK_TYPE_LIST)
                return $this->getListContent($id, $locale, true);
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
