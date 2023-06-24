<?php

namespace wdmg\api\controllers\api;

use yii\base\BaseObject;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use wdmg\api\controllers\RestController;
use Yii;
use yii\web\User;

class UsersController extends RestController
{

    /**
     * {@inheritdoc}
     */
    public function init()
    {

        $module_id = 'users';
        if($this->module->module)
            $module_id = $this->module->module->id . '/' . $module_id;

        $this->modelClass = new BaseObject();
        if(class_exists('\wdmg\users\models\Users') && Yii::$app->hasModule($module_id))
            $this->modelClass = 'wdmg\api\models\api\UsersAPI';
        else
            throw new NotFoundHttpException('Requested API not found.');

	    parent::init();
    }

	/**
	 * {@inheritdoc}
	 */
	public function checkAccess($action, $model = null, $params = [])
	{

		if ($this->modelClass)
			parent::checkAccess($action, $this->modelClass, $params);

		if (is_null($model) && $action !== 'index')
			throw new ForbiddenHttpException(Yii::t('app/modules/api', 'Access to this API has not supported.', null, $this->getAcceptLanguage()), -1);

		switch ($action) {

			case 'index':

				// Check if the current user has permission to view list of all users
				if (!Yii::$app->getUser()->can('admin'))
					throw new ForbiddenHttpException(Yii::t('app/modules/api', 'You are not allowed to list users', null, $this->getAcceptLanguage()), -1);

				break;

			case 'create':

				$model->scenario = $this->createScenario;

				break;

			case 'view':
			case 'update':
			case 'delete':

				if ($action == 'update')
					$model->scenario = $this->updateScenario;

				// Check if the current user has permission to view yourself
				if (!($model->getUserId() == $this->getAuthUserId() || Yii::$app->getUser()->can('admin')))
					throw new ForbiddenHttpException(Yii::t('app/modules/api', 'You are not allowed to view user ID: {user}', ['user' => $model->getId()], $this->getAcceptLanguage()));

				break;

			default:

				// Other non admin permissions
				if (!Yii::$app->getUser()->can('admin'))
					throw new ForbiddenHttpException(Yii::t('app/modules/api', 'You are not allowed to run this action: {action}', ['action' => $action], $this->getAcceptLanguage()));

				break;
		}

		if (Yii::$app->getUser()->can('admin')) {
			Yii::$app->response->headers->set('X-Action-Params', json_encode([
				'action' => $action,
				'model' => $model,
				'scenario' => $model->scenario,
				'scenarios' => [
					'create' => $this->createScenario,
					'update' => $this->updateScenario,
				],
				'params' => $params
			]));
		}
	}
}

?>