<?php

namespace wdmg\api\models\api;

use Yii;
use wdmg\users\models\Users;

class UsersAPI extends Users
{
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['auth_key'], $fields['password_hash'], $fields['email_confirm_token'], $fields['password_reset_token']);
        return $fields;
    }

	/**
	 * {@inheritdoc}
	 */
	public function getUserId()
	{
		return $this->id;
	}
}
