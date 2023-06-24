<?php

namespace wdmg\api\controllers;

use wdmg\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\ContentNegotiator;
use yii\filters\AccessControl;
use yii\filters\RateLimiter;
use yii\helpers\Console;
use yii\rest\ActiveController;
use Yii;
use wdmg\api\models\API;
use wdmg\api\ErrorAction;
use wdmg\users\models\Users;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use yii\web\ForbiddenHttpException;


class RestController extends ActiveController
{
    public $modelClass;
    private $acceptLanguage = 'en-US';
    private $accessToken = null;
    private $accessExpired = null;
    private $requestMode = null;
    private $allowedModes = [];
    private $allowedModels = [];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

	    \Yii::$app->user->enableSession = false;

        // Get request modes
        $this->requestMode = 'public';

        if ($headers = Yii::$app->request->getHeaders()) {
	        if (!empty($headers['accept-language']) || !empty(Yii::$app->request->get('_lang', 'en'))) {

		        $locales = [];

				if (!empty($headers['accept-language']))
			        $lang = $headers['accept-language'];
				else if (!empty(Yii::$app->request->get('_lang', 'en')))
			        $lang = Yii::$app->request->get('_lang', 'en');

		        if (isset(Yii::$app->translations) && class_exists('wdmg\translations\models\Languages')) {
			        $locales = Yii::$app->translations->getLocales(true, false, true);
			        $locales = ArrayHelper::map($locales, 'url', 'locale');
		        } else if (isset($this->module->supportLocales)) {
			        $supportLocales = $this->module->supportLocales;
			        foreach ($supportLocales as $locale) {
				        if ($lang === \Locale::getPrimaryLanguage($locale)) {
					        $locales[$lang] = $locale;
					        break;
				        }
			        }
		        }

		        if (!empty($locales[$lang])) {
			        $this->acceptLanguage = $locales[$lang];
			        Yii::$app->language = $this->acceptLanguage;
		        }
	        }

	        if (!empty($headers['authorization']))
		        $this->requestMode = 'private';

        }

		if (!empty(Yii::$app->request->get('access-token', ''))) {
	        $this->requestMode = 'private';
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function verbs()
    {
        $verbs = array_merge(parent::verbs(), [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['POST', 'PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ]);

        if (!$this->requestMode == 'private') {
	        $verbs['update'] = [];
	        $verbs['create'] = [];
	        $verbs['delete'] = [];
        }

        return $verbs;
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => []
        ];

        // Get allowed modes
        if (isset(Yii::$app->params['api.allowedApiModes']))
            $this->allowedModes = Yii::$app->params['api.allowedApiModes'];
        else
            $this->allowedModes = Yii::$app->controller->module->allowedApiModes;

        // Get allowed models
        if (isset(Yii::$app->params['api.allowedApiModels']))
            $this->allowedModels = Yii::$app->params['api.allowedApiModels'];
        else
            $this->allowedModels = Yii::$app->controller->module->allowedApiModels;

        // Get auth methods
        if (isset(Yii::$app->params['api.authMethods']))
            $authMethods = Yii::$app->params['api.authMethods'];
        else
            $authMethods = Yii::$app->controller->module->authMethods;

        if ($this->requestMode == 'private' && isset($authMethods['basicAuth'])) {
            if ($authMethods['basicAuth'] == true) {
	            $behaviors['authenticator']['authMethods'][] = [
		            'class' => HttpBasicAuth::class,
		            'auth' => [$this, 'auth'],
		            'realm' => 'api'
	            ];
            }
        }

		if ($this->requestMode == 'private' && isset($authMethods['bearerAuth'])) {
	        if ($authMethods['bearerAuth'] == true) {
		        $behaviors['authenticator']['authMethods'][] = [
			        'class' => HttpBearerAuth::class,
			        'header' => 'Authorization',
			        'pattern' => '/^Bearer\s+(.*?)$/',
			        'realm' => 'api'
		        ];
	        }
        }

		if ($this->requestMode == 'private' && isset($authMethods['paramAuth'])) {
            if ($authMethods['paramAuth'] == true) {
	            $behaviors['authenticator']['authMethods'][] = [
		            'class' => QueryParamAuth::class,
		            'tokenParam' => 'access-token'
	            ];
            }
        }

        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/xml' => Response::FORMAT_XML,
            ]
        ];

        // Rate limit headers send?
        if (isset(Yii::$app->params['api.rateLimitHeaders']))
            $rateLimitHeaders = intval(Yii::$app->params['api.rateLimitHeaders']);
        else
            $rateLimitHeaders = Yii::$app->controller->module->rateLimitHeaders;

        if ($rateLimitHeaders == true)
            $behaviors['rateLimiter']['enableRateLimitHeaders'] = true;
        else
            $behaviors['rateLimiter']['enableRateLimitHeaders'] = false;

        // Get blocked IP`s
        if (isset(Yii::$app->params['api.blockedIp']))
            $blockedIp = Yii::$app->params['api.blockedIp'];
        else
            $blockedIp = Yii::$app->controller->module->blockedIp;

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ($this->requestMode == 'private') ? ['@'] : '',
                    'matchCallback' => function ($rule, $action) use ($blockedIp) {
                        if (Yii::$app->request->userIP) {
                            if (is_array($blockedIp)) {
                                return (!in_array(Yii::$app->request->userIP, $blockedIp));
                            } else {
                                return (!strpos(Yii::$app->request->userIP, $blockedIp));
                            }
                        }
                        return true;
                    }
                ]
            ],
            'denyCallback' => function ($rule, $action) {
                throw new ForbiddenHttpException(Yii::t('app/modules/api', 'Access to API has blocked.'), -2);
            }
        ];

	    // Config support locales
	    $locales = [];
	    if (isset(Yii::$app->translations) && class_exists('wdmg\translations\models\Languages')) {
		    $locales = Yii::$app->translations->getLocales(true, false, true);
		    $locales = ArrayHelper::map($locales, 'url', 'locale');
	    } else if (isset($this->module->supportLocales)) {
		    $supportLocales = $this->module->supportLocales;
		    foreach ($supportLocales as $locale) {
			    if ($lang === \Locale::getPrimaryLanguage($locale)) {
				    $locales[$lang] = $locale;
				    break;
			    }
		    }
	    }

	    if (!empty($locales))
		    $behaviors['contentNegotiator']['languages'] = array_keys($locales);

	    Yii::$app->response->headers->set('X-Access-Mode', $this->requestMode);
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAccess($action, $model = null, $params = [])
    {

	    if (is_null($model) && $this->modelClass)
		    $model = $this->modelClass;

	    if (empty($params))
	        $params = Yii::$app->request->bodyParams;

	    if ($this->modelClass)
		    parent::checkAccess($action, $this->modelClass, $params);

        if ($this->requestMode == 'public' && isset($this->allowedModes['public'])) {
            if ($this->allowedModes['public'] === false)
                throw new ForbiddenHttpException(Yii::t('app/modules/api', 'Access to all public API has disabled.', null, $this->acceptLanguage), -2);
        } else if ($this->requestMode == 'private' && isset($this->allowedModes['private'])) {
            if ($this->allowedModes['private'] === false)
                throw new ForbiddenHttpException(Yii::t('app/modules/api', 'Access to all private API has disabled.', null, $this->acceptLanguage), -2);
        } else {
            throw new InvalidConfigException(Yii::t('app/modules/api', 'Requested invalid configuration of API.', null, $this->acceptLanguage), 0);
        }

        if ($this->requestMode == 'public') {
            if (isset($this->allowedModes['public']) && isset($this->allowedModels['public'][$model])) {
                if ($this->allowedModes['public'] === true && $this->allowedModels['public'][$model] === false)
                    throw new ForbiddenHttpException(Yii::t('app/modules/api', 'Access to this public API has disabled.', null, $this->acceptLanguage), -3);
            } else {
                throw new ForbiddenHttpException(Yii::t('app/modules/api', 'Access to this API has not supported.', null, $this->acceptLanguage), -1);
            }
        } else if ($this->requestMode == 'private') {
            if (isset($this->allowedModes['private']) && isset($this->allowedModels['private'][$model])) {
                if ($this->allowedModes['private'] === true && $this->allowedModels['private'][$model] === false)
                    throw new ForbiddenHttpException(Yii::t('app/modules/api', 'Access to this private API has disabled.', null, $this->acceptLanguage), -3);
            } else {
                throw new ForbiddenHttpException(Yii::t('app/modules/api', 'Access to this API has not supported.', null, $this->acceptLanguage), -1);
            }
        } else {
            throw new ForbiddenHttpException(Yii::t('app/modules/api', 'Access to API has blocked.', null, $this->acceptLanguage), -2);
        }

	    if ($this->requestMode == 'private') {

		    if (defined("$model::SCENARIO_CREATE_REST"))
			    $this->createScenario = $model::SCENARIO_CREATE_REST;
		    else if (defined("$model::SCENARIO_DEFAULT"))
			    $this->createScenario = $model::SCENARIO_DEFAULT;

		    if (defined("$model::SCENARIO_UPDATE_REST"))
			    $this->updateScenario = $model::SCENARIO_UPDATE_REST;
		    else if (defined("$model::SCENARIO_DEFAULT"))
			    $this->updateScenario = $model::SCENARIO_DEFAULT;

	    }
	}

    /**
     * BaseAuth
     */
    public function auth($username, $password)
    {

	    $user = Users::findOne(['username' => $username]);

		if (is_null($user)) {

			// Log activity
			$this->module->logActivity(
				'API: User not found (Basic Auth). User `'.$username.'`',
				$this->uniqueId . ":" . $this->action->id,
				'error',
				1
			);

			throw new ForbiddenHttpException(Yii::t('app/modules/api', 'User not found.'), -2);
		} else {
			if ($user->validatePassword($password)) {

				$sendAccessToken = true;
				if ($client = Api::findIdentity($user->id)) {
					$this->accessToken = $client->access_token;
					$this->accessExpired = $client->expired_at;

					// Log activity
					$this->module->logActivity(
						'API: Successful login (Basic Auth). User `'.$username.'`,  ID: ' . $user->id,
						$this->uniqueId . ":" . $this->action->id,
						'info',
						1
					);

					return $client;
				} else {
					$client = new API();
					if ($client->addNewIdentity($user->id)) {
						$this->accessToken = $client->access_token;
						$this->accessExpired = $client->expired_at;

						// Log activity
						$this->module->logActivity(
							'API: Add new identity and successful login (Basic Auth). User `'.$username.'`,  ID: ' . $user->id,
							$this->uniqueId . ":" . $this->action->id,
							'success',
							1
						);

						return $client;
					}
				}


				// Log activity
				$this->module->logActivity(
					'API: Other auth error (Basic Auth). User `'.$username.'`',
					$this->uniqueId . ":" . $this->action->id,
					'error',
					1
				);

				return null;
			} else {

				// Log activity
				$this->module->logActivity(
					'API: Password is wrong (Basic Auth). User `'.$username.'`',
					$this->uniqueId . ":" . $this->action->id,
					'error',
					1
				);

				throw new ForbiddenHttpException(Yii::t('app/modules/api', 'Password is wrong.'), -2);
			}
		}

        return null;
    }

	/**
	 * {@inheritdoc}
	 */
	public function actions()
	{
		return parent::actions();
	}

	/**
	 * {@inheritdoc}
	 */
	public function afterAction($action, $result)
	{
		$result = parent::afterAction($action, $result);

		// Send access token in header
		if (isset(Yii::$app->params['api.sendAccessToken']))
			$sendAccessToken = intval(Yii::$app->params['api.sendAccessToken']);
		else
			$sendAccessToken = Yii::$app->controller->module->sendAccessToken;

		if ($sendAccessToken && $this->requestMode == 'private' && $this->accessToken) {
			Yii::$app->response->headers->set('X-Access-Token', $this->accessToken);
			Yii::$app->response->headers->set('X-Access-Expired', $this->accessExpired);
		}

		Yii::$app->response->headers->remove('Link');
		Yii::$app->response->headers->remove('X-Pagination-Total-Count');
		Yii::$app->response->headers->remove('X-Pagination-Page-Count');
		Yii::$app->response->headers->remove('X-Pagination-Current-Page');
		Yii::$app->response->headers->remove('X-Pagination-Per-Page');

		return $this->serializeData($result);
	}

	public function getAcceptLanguage() {
		return $this->acceptLanguage;
	}

	public function getAuthUserId() {
		return Yii::$app->getUser()->getIdentity(false)->getId();
	}
}

?>