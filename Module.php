<?php

namespace wdmg\api;

/**
 * Yii2 API
 *
 * @category        Module
 * @version         1.2.10
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
     * @var string the module version
     */
    private $version = "1.2.10";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 10;

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


                    '<module:api>/<controller:\w+>/' => 'admin/<module>/<controller>',
                    '<module:api>/<controller:\w+>/<action:\w+>/' => 'admin/<module>/<controller>/<action>',
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