<?php

namespace wdmg\api\models\api;

use Yii;
use wdmg\users\models\Users;

class UsersAPI extends Users
{
	const SCENARIO_CREATE_REST = 'create_user_rest';
	const SCENARIO_UPDATE_REST = 'update_user_rest';

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['auth_key'], $fields['password_hash'], $fields['email_confirm_token'], $fields['password_reset_token']);
        return $fields;
    }

	/**
	 * {@inheritdoc}
	 */
	public function scenarios()
	{
		$scenarios = parent::scenarios();
		$scenarios[self::SCENARIO_CREATE_REST] = ['username', 'email', 'password', 'password_confirm'];
		$scenarios[self::SCENARIO_UPDATE_REST] = ['email', 'password', 'password_confirm'];
		return $scenarios;
	}
}
