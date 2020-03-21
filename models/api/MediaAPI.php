<?php

namespace wdmg\api\models\api;

use Yii;
use wdmg\media\models\Media;

class MediaAPI extends Media
{
    private $allowedFields = [
        'id',
        'cat_id',
        'name',
        'alias',
        'path',

        'url',
        'thumbnail',

        'size',
        'title',
        'caption',
        'description',
        'mime_type',

        /*'params',
        'reference',*/

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

        if (!isset($fields['url'])) {
            $fields['url'] = function() {
                return $this->getUrl();
            };
        }

        if (!isset($fields['thumbnail'])) {
            $fields['thumbnail'] = function() {
                return $this->getThumbnail();
            };
        }

        foreach ($fields as $key => $field) {
            if (!in_array($key, $this->allowedFields))
                unset($fields[$key]);
        }

        return $fields;
    }

    public function extraFields()
    {
        return [
            'categories',
            /*'created_by' => function() {
                return $this->getCreatedBy();
            },
            'updated_by' => function() {
                return $this->getUpdatedBy();
            },*/
        ];
    }
}