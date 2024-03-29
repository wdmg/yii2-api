<?php

namespace wdmg\api;

/**
 * Yii2 API
 *
 * @category        Module
 * @version         2.0.1
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-api
 * @copyright       Copyright (c) 2019 - 2023 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use wdmg\helpers\ArrayHelper;
use Yii;
use wdmg\base\BaseModule;

/**
 * api module definition class
 */
class Module extends BaseModule
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'wdmg\api\controllers';

    /**
     * {@inheritdoc}
     */
    public $defaultRoute = 'api/index';

    /**
     * @var string, the name of module
     */
    public $name = "API";

    /**
     * @var string, the description of module
     */
    public $description = "API control module with testing tool";

    /**
     * @var string the module version
     */
    private $version = "2.0.1";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 10;

    /**
     * @var integer, lifetime of `acces_token` by default, `0` - unlimited
     */
    public $accessTokenExpire = 3600;

    /**
     * @var integer, request`s to API per minute by default
     */
    public $rateLimit = 30;

    /**
     * @var boolean, sent rate limit with HTTP-headers
     */
    public $rateLimitHeaders = false;

    /**
     * @var array of the allowed auth methods
     */
    public $authMethods = [
        'basicAuth' => true,
        'bearerAuth' => true,
        'paramAuth' => true
    ];

    /**
     * @var array of the allowed API modes
     */
    public $allowedApiModes = [
        'public' => true,
        'private' => true
    ];

    /**
     * @var array of the allowed API models
     */
    public $allowedApiModels = [
        'public' => [
            "wdmg\api\models\api\MailerAPI" => false,
            "wdmg\api\models\api\NewsAPI" => true,
            "wdmg\api\models\api\BlogAPI" => true,
            "wdmg\api\models\api\OptionsAPI" => false,
            "wdmg\api\models\api\ContentAPI" => true,
            "wdmg\api\models\api\PagesAPI" => true,
            "wdmg\api\models\api\MediaAPI" => true,
            "wdmg\api\models\api\SearchAPI" => true,
            "wdmg\api\models\api\MenuAPI" => true,
            "wdmg\api\models\api\CommentsAPI" => true,
            "wdmg\api\models\api\LiveSearchAPI" => false,
            "wdmg\api\models\api\RedirectsAPI" => false,
            "wdmg\api\models\api\StatsAPI" => false,
            "wdmg\api\models\api\TasksAPI" => false,
            "wdmg\api\models\api\TicketsAPI" => false,
            "wdmg\api\models\api\UsersAPI" => false,
            "wdmg\api\models\api\SubscribersAPI" => false,
            "wdmg\api\models\api\SubscribersListAPI" => false,
            "wdmg\api\models\api\NewslettersAPI" => false,
        ],
        'private' => [
            "wdmg\api\models\api\MailerAPI" => true,
            "wdmg\api\models\api\NewsAPI" => true,
            "wdmg\api\models\api\BlogAPI" => true,
            "wdmg\api\models\api\OptionsAPI" => true,
            "wdmg\api\models\api\ContentAPI" => true,
            "wdmg\api\models\api\PagesAPI" => true,
            "wdmg\api\models\api\MediaAPI" => true,
            "wdmg\api\models\api\SearchAPI" => true,
            "wdmg\api\models\api\MenuAPI" => true,
            "wdmg\api\models\api\CommentsAPI" => true,
            "wdmg\api\models\api\LiveSearchAPI" => true,
            "wdmg\api\models\api\RedirectsAPI" => true,
            "wdmg\api\models\api\StatsAPI" => true,
            "wdmg\api\models\api\TasksAPI" => true,
            "wdmg\api\models\api\TicketsAPI" => true,
            "wdmg\api\models\api\UsersAPI" => true,
            "wdmg\api\models\api\SubscribersAPI" => true,
            "wdmg\api\models\api\SubscribersListAPI" => true,
            "wdmg\api\models\api\NewslettersAPI" => true,
        ],
    ];

    /**
     * @var boolean, send access token with HTTP-headers
     */
    public $sendAccessToken = true;

    /**
     * @var array, blocked access from IP`s
     */
    public $blockedIp = [];

	/**
	 * @var array, the list of support locales for multi-language versions of posts.
	 * @note This variable will be override if you use the `wdmg\yii2-translations` module.
	 */
	public $supportLocales = ['ru-RU', 'uk-UA', 'en-US'];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Set version of current module
        $this->setVersion($this->version);

        // Set priority of current module
        $this->setPriority($this->priority);
    }

    /**
     * {@inheritdoc}
     */
    public function dashboardNavItems($options = null)
    {
        $items = [
            'label' => $this->name,
            'url' => '#',
            'icon' => 'fa fa-fw fa-plug',
            'active' => in_array(\Yii::$app->controller->module->id, [$this->id]),
            'items' => [
                [
                    'label' => Yii::t('app/modules/api', 'List of API`s'),
                    'url' => [$this->routePrefix . '/api/'],
                    'active' => (in_array(\Yii::$app->controller->module->id, ['api']) &&  Yii::$app->controller->id == 'api'),
                ],
                [
                    'label' => Yii::t('app/modules/api', 'Access to API`s'),
                    'url' => [$this->routePrefix . '/api/access/'],
                    'active' => (in_array(\Yii::$app->controller->module->id, ['api']) &&  Yii::$app->controller->id == 'access'),
                ],
            ]
        ];

	    if (!is_null($options)) {

		    if (isset($options['count'])) {
			    $items['label'] .= '<span class="badge badge-default float-right">' . $options['count'] . '</span>';
			    unset($options['count']);
		    }

		    if (is_array($options))
			    $items = ArrayHelper::merge($items, $options);

	    }

	    return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        // Get the module instance
        //$module = $app->getModule('admin/api');
        $module = $this;

        // Configure urlManager and Request component
        if (!Yii::$app instanceof \yii\console\Application) {
            $app->getRequest()->parsers[] = ['application/json' => 'yii\web\JsonParser'];
            $app->getRequest()->enableCookieValidation = false;
            $app->getRequest()->enableCsrfValidation = false;
	        $app->getRequest()->enableCsrfCookie = false;
            Yii::$app->response->cookies->removeAll();

            // Configure API-auth component
            $request = new \yii\web\Request(['url' => parse_url(Yii::$app->request->getUrl(), PHP_URL_PATH)]);
            if (preg_match('/^\/api\/?+/is', $request->url)) {
                $module->controllerNamespace = 'wdmg\api\controllers\api';
                $app->setComponents([
                    'user' => [
                        'class' => '\yii\web\User',
                        'identityClass' => 'wdmg\api\models\API',
                        'enableAutoLogin' => false,
                        'enableSession' => false,
                    ], [
		                'class' => 'yii\filters\ContentNegotiator',
		                'formats' => [
			                'application/json' => yii\web\Response::FORMAT_JSON,
                            'application/xml' => yii\web\Response::FORMAT_XML,
		                ],
		                'languages' => [
			                'en',
			                'ru',
			                'uk',
		                ]
	                ]
                ]);

                $app->getUrlManager()->addRules([
                    [
                        'class' => 'yii\rest\UrlRule',
                        'controller' => [
                            'users',
                            'options',
                        ]
                    ],


                    '<module:api>/<controller:[\w-]+>/' => 'admin/<module>/<controller>',
                    '<module:api>/<controller:[\w-]+>/<action:[\w-]+>/' => 'admin/<module>/<controller>/<action>',
                    [
                        'pattern' => '<module:api>/<controller:[\w-]+>/',
                        'route' => 'admin/<module>/<controller>',
                        'suffix' => '',
                    ],[
                        'pattern' => '<module:api>/<controller:[\w-]+>/<action:\w+>/',
                        'route' => 'admin/<module>/<controller>/<action>',
                        'suffix' => '',
                    ],


                ], false);

				if (strpos(Yii::$app->request->headers['accept'], 'json') != false)
					Yii::$app->response->format = 'json';
				else if (strpos(Yii::$app->request->headers['accept'], 'xml') != false)
					Yii::$app->response->format = 'xml';

            }
        }

        // Get URL path prefix if exist
        if (isset($module->routePrefix))
            $prefix = $module->routePrefix . '/';
        else
            $prefix = '';

        // Add module URL rules
        $app->getUrlManager()->addRules(
            [
                $prefix . '<module:api>' => '<module>/api/index',
                $prefix . '<module:api>/<controller:[\w-]+>' => '<module>/<controller>',
                $prefix . '<module:api>/<controller:[\w-]+>/<action:[0-9a-zA-Z_\-]+>' => '<module>/<controller>/<action>',
                $prefix . '<module:api>/<controller:[\w-]+>/<action:[0-9a-zA-Z_\-]+>/<id:\d+>' => '<module>/<controller>/<action>',
                [
                    'pattern' => $prefix . '<module:api>/',
                    'route' => '<module>/api/index',
                    'suffix' => ''
                ], [
                    'pattern' => $prefix . '<module:api>/<controller:[\w-]+>/',
                    'route' => '<module>/<controller>',
                    'suffix' => ''
                ], [
                    'pattern' => $prefix . '<module:api>/<controller:[\w-]+>/<action:[0-9a-zA-Z_\-]+>/',
                    'route' => '<module>/<controller>/<action>',
                    'suffix' => ''
                ], [
                    'pattern' => $prefix . '<module:api>/<controller:[\w-]+>/<action:[0-9a-zA-Z_\-]+>/<id:\d+>/',
                    'route' => '<module>/<controller>/<action>',
                    'suffix' => ''
                ]
            ],
            true
        );

    }
}