<?php

namespace wdmg\api;

/**
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @copyright       Copyright (c) 2019 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 */

use yii\base\BootstrapInterface;
use Yii;
use wdmg\api\components\API;


class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        // Get the module instance
        $module = Yii::$app->getModule('api');

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
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'users',
                        'options',
                    ],
                    /*'except' => ['delete'],*/
                    /*'tokens' => [
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
                    /*'extraPatterns' => [
                        '/api/users' => '<module>/<controller>'
                    ]*/
                ],
                $prefix . '<module:api>/' => '<module>/api/index',
                $prefix . '<module:api>/<controller:\w+>/' => '<module>/<controller>',
                $prefix . '<module:api>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
                [
                    'pattern' => $prefix . '<module:api>/',
                    'route' => '<module>/api/index',
                    'suffix' => '',
                ], [
                    'pattern' => $prefix . '<module:api>/<controller:\w+>/',
                    'route' => '<module>/<controller>',
                    'suffix' => '',
                ], [
                    'pattern' => $prefix . '<module:api>/<controller:\w+>/<action:\w+>',
                    'route' => '<module>/<controller>/<action>',
                    'suffix' => '',
                ],
                '<module:api>/<controller:\w+>/' => '<module>/<controller>',
                '<module:api>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
                '<module:api>/<controller:\w+>/<action:\w+>/<id:\d+>' => '<module>/<controller>/<action>',
                [
                    'pattern' => '<module:api>/<controller:\w+>/',
                    'route' => '<module>/<controller>',
                    'suffix' => '',
                ], [
                    'pattern' => '<module:api>/<controller:\w+>/<action:\w+>',
                    'route' => '<module>/<controller>/<action>',
                    'suffix' => '',
                ], [
                    'pattern' => '<module:api>/<controller:\w+>/<action:\w+>/<id:\d+>',
                    'route' => '<module>/<controller>/<action>',
                    'suffix' => '',
                ]
            ],
            true
        );

        // Configure urlManager and Request component
        if (!Yii::$app instanceof \yii\console\Application)
            $app->getRequest()->parsers[] = ['application/json' => 'yii\web\JsonParser'];

        // Configure options component
        $app->setComponents([
            'api' => [
                'class' => API::className()
            ]
        ]);
    }
}