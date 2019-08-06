<?php

namespace wdmg\api\models\api;

use Yii;
use wdmg\mailer\models\Mails;

class MailerAPI extends Mails
{
    public function fields()
    {
        $fields = parent::fields();
        return $fields;
    }
}
