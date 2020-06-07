<?php

namespace wdmg\api\models\api;

use Yii;
use wdmg\comments\models\Comments;

class CommentsAPI extends Comments
{
    public function init()
    {
        $this->scenario = 'default';
        parent::init();
    }

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['session']);
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
