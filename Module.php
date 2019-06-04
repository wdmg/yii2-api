<?php

namespace wdmg\api;

/**
 * Yii2 API
 *
 * @category        Module
 * @version         1.2.6
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-api
 * @copyright       Copyright (c) 2019 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

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
     * @var string the prefix for routing of module
     */
    public $routePrefix = "admin";

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
     * @var array, allowed auth methods
     */
    public $authMethods = [
        'basicAuth' => true,
        'bearerAuth' => true,
        'paramAuth' => true
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
     * @var string, the name of module
     */
    public $name = "API";

    /**
     * @var string, the description of module
     */
    public $description = "API control module";

    /**
     * @var string the vendor name of module
     */
    private $vendor = "wdmg";

    /**
     * @var string the module version
     */
    private $version = "1.2.6";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 10;

    /**
     * @var array of strings missing translations
     */
    public $missingTranslation;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Set controller namespace for console commands
        if (Yii::$app instanceof \yii\console\Application)
            $this->controllerNamespace = 'wdmg\api\commands';

        // Set current version of module
        $this->setVersion($this->version);

        // Register translations
        $this->registerTranslations();

        // Normalize route prefix
        $this->routePrefixNormalize();
    }

    /**
     * Return module vendor
     * @var string of current module vendor
     */
    public function getVendor() {
        return $this->vendor;
    }

    /**
     * {@inheritdoc}
     */
    public function afterAction($action, $result)
    {

        // Log to debuf console missing translations
        if (is_array($this->missingTranslation) && YII_ENV == 'dev')
            Yii::warning('Missing translations: ' . var_export($this->missingTranslation, true), 'i18n');

        $result = parent::afterAction($action, $result);
        return $result;

    }

    // Registers translations for the module
    public function registerTranslations()
    {
        Yii::$app->i18n->translations['app/modules/api'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@vendor/wdmg/yii2-api/messages',
            'on missingTranslation' => function($event) {

                if (YII_ENV == 'dev')
                    $this->missingTranslation[] = $event->message;

            },
        ];

        // Name and description translation of module
        $this->name = Yii::t('app/modules/api', $this->name);
        $this->description = Yii::t('app/modules/api', $this->description);
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('app/modules/api' . $category, $message, $params, $language);
    }

    /**
     * Normalize route prefix
     * @return string of current route prefix
     */
    public function routePrefixNormalize()
    {
        if(!empty($this->routePrefix)) {
            $this->routePrefix = str_replace('/', '', $this->routePrefix);
            $this->routePrefix = '/'.$this->routePrefix;
            $this->routePrefix = str_replace('//', '/', $this->routePrefix);
        }
        return $this->routePrefix;
    }

    /**
     * Build dashboard navigation items for NavBar
     * @return array of current module nav items
     */
    public function dashboardNavItems()
    {
        return [
            'label' => $this->name,
            'url' => [$this->routePrefix . '/api/'],
            'active' => in_array(\Yii::$app->controller->module->id, ['api'])
        ];
    }

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
                    ]
                ]);
                $app->getUrlManager()->addRules([
                    [
                        'class' => 'yii\rest\UrlRule',
                        'controller' => [
                            'users',
                            'options',
                        ],
                        /*'except' => ['delete'],
                        'tokens' => [
                            '{id}' => '<id:\\w+>'
                        ],
                        'extraPatterns' => [
                            'POST register' => 'register', //from url
                            'GET exists'=>'exists',
                            'POST login'=>'login',
                            'POST follow'=>'follow',
                            'POST category'=>'category',
                            'PUT profile'=>'profile',
                            'PUT change_password'=>'change_password',
                            'PUT feed_interested'=>'feed_interested',
                        ],*/
                        /*'pluralize' => false,*/
                        'extraPatterns' => [
                            /*[
                                'pattern' => '<module:api>/<controller:(users|options)>',
                                'route' => '<module>/<controller>',
                                'prefix' => '/api',
                            ],*/
                        ]
                    ],


                    '<module:api>/<controller:\w+>/' => '<module>/<controller>',
                    '<module:api>/<controller:\w+>/<action:\w+>/' => '<module>/<controller>/<action>',
                    [
                        'pattern' => '<module:api>/<controller:\w+>/',
                        'route' => 'admin/<module>/<controller>',
                        'suffix' => '',
                    ],[
                        'pattern' => '<module:api>/<controller:\w+>/<action:\w+>/',
                        'route' => 'admin/<module>/<controller>/<action>',
                        'suffix' => '',
                    ],


                ], false);
            }
        }
        // Get URL path prefix if exist
        if (isset($module->routePrefix)) {
            $app->getUrlManager()->enableStrictParsing = true;
            $prefix = $module->routePrefix . '/';
        } else {
            $prefix = '';
        }

        // Add module URL rules
        $app->getUrlManager()->addRules(
            [
                $prefix . '<module:api>' => '<module>/api/index',
                $prefix . '<module:api>/<controller:\w+>' => '<module>/<controller>',
                $prefix . '<module:api>/<controller:\w+>/<action:[0-9a-zA-Z_\-]+>' => '<module>/<controller>/<action>',
                $prefix . '<module:api>/<controller:\w+>/<action:[0-9a-zA-Z_\-]+>/<id:\d+>' => '<module>/<controller>/<action>',
                [
                    'pattern' => $prefix . '<module:api>/',
                    'route' => '<module>/api/index',
                    'suffix' => ''
                ], [
                    'pattern' => $prefix . '<module:api>/<controller:\w+>/',
                    'route' => '<module>/<controller>',
                    'suffix' => ''
                ], [
                    'pattern' => $prefix . '<module:api>/<controller:\w+>/<action:[0-9a-zA-Z_\-]+>/',
                    'route' => '<module>/<controller>/<action>',
                    'suffix' => ''
                ], [
                    'pattern' => $prefix . '<module:api>/<controller:\w+>/<action:[0-9a-zA-Z_\-]+>/<id:\d+>/',
                    'route' => '<module>/<controller>/<action>',
                    'suffix' => ''
                ]
            ],
            true
        );
    }
}