[![Yii2](https://img.shields.io/badge/required-Yii2_v2.0.35-blue.svg)](https://packagist.org/packages/yiisoft/yii2)
[![Downloads](https://img.shields.io/packagist/dt/wdmg/yii2-api.svg)](https://packagist.org/packages/wdmg/yii2-api)
[![Packagist Version](https://img.shields.io/packagist/v/wdmg/yii2-api.svg)](https://packagist.org/packages/wdmg/yii2-api)
![Progress](https://img.shields.io/badge/progress-ready_to_use-green.svg)
[![GitHub license](https://img.shields.io/github/license/wdmg/yii2-api.svg)](https://github.com/wdmg/yii2-api/blob/master/LICENSE)

<img src="./docs/images/yii2-api.png" width="100%" alt="Yii2 API module" />

# Yii2 API
API control module for Yii2.
This module is an integral part of the [Butterfly.СMS](https://butterflycms.com/) content management system, but can also be used as an standalone extension.
Copyrights (c) 2019-2020 [W.D.M.Group, Ukraine](https://wdmg.com.ua/)

# Requirements 
* PHP 5.6 or higher
* Yii2 v.2.0.35 and newest
* [Yii2 Base](https://github.com/wdmg/yii2-base) module (required)
* [Yii2 Users](https://github.com/wdmg/yii2-users) module (required)
* [Yii2 Options](https://github.com/wdmg/yii2-options) module (support)
* [Yii2 SelectInput](https://github.com/wdmg/yii2-selectinput) widget
* [ClipboardJS](https://github.com/zenorocha/clipboard.js) asset library (required)

# Installation
To install the module, run the following command in the console:

`$ composer require "wdmg/yii2-api"`

After configure db connection, run the following command in the console:

`$ php yii api/init`

And select the operation you want to perform:
  1) Apply all module migrations
  2) Revert all module migrations

# Migrations
In any case, you can execute the migration and create the initial data, run the following command in the console:

`$ php yii migrate --migrationPath=@vendor/wdmg/yii2-api/migrations`

# Configure
To add a module to the project, add the following data in your configuration file:

    'modules' => [
        ...
        'api' => [
            'class' => 'wdmg\api\Module',
            'routePrefix' => 'admin', // routing prefix for dashboard
            'accessTokenExpire', => 3600 // lifetime of `access_token`, `0` - unlimited
            'blockedIp' => [], // array, blocked access from IP`s
            'rateLimit' => 30, // request`s to API per minute
            'rateLimitHeaders' => false, // send HTTP-headers of rate limit
            'sendAccessToken' => true, // send access token with HTTP-headers
            'authMethods' => [ // auth methods to allow
                'basicAuth' => true,
                'bearerAuth' => true,
                'paramAuth' => true
            ],
            'authMethods' => [ // allowed API modes
                'public' => true,
                'private' => true
            ],
            'allowedApiModels' => [ // allowed API models
                'public' => [
                    "wdmg\api\models\api\NewsAPI" => true,
                    "wdmg\api\models\api\OptionsAPI" => false,
                    "wdmg\api\models\api\PagesAPI" => false,
                    "wdmg\api\models\api\UsersAPI" => false,
                    ...
                ],
                'private' => [
                    "wdmg\api\models\api\NewsAPI" => true,
                    "wdmg\api\models\api\OptionsAPI" => true,
                    "wdmg\api\models\api\PagesAPI" => true,
                    "wdmg\api\models\api\UsersAPI" => true,
                    ...
                ],
            ]
        ],
        ...
    ],

# Usecase

Request to API with base auth by username and password (option `authMethods['basicAuth']` must be set to `true`):

    $ curl 'http://example.com/api/users' \
    -XGET \
    -H 'Accept: application/json, text/javascript, */*; q=0.01' \
    -H 'Authorization: Basic YOUR_USERNAME_AND_PASSWORD'
    
<b>Attention!</b> YOUR_USERNAME_AND_PASSWORD in format `username:password` and has been encoded, like `base64_encode('username:password')`
After successful authorization, the server will return `X-Access-Token` for use in other requests (option `sendAccessToken` must be set to `true`).


Request to API with query param `access_token` (option `authMethods['paramAuth']` must be set to `true`):

    $ curl 'http://example.com/api/users?access-token=YOUR_API_ACCESS_TOKEN' \
    -XGET \
    -H 'Accept: application/json, text/javascript, */*; q=0.01'

Request to API with bearer `access_token` (option `authMethods['bearerAuth']` must be set to `true`):

    $ curl 'http://example.com/api/users' \
    -XGET \
    -H 'Accept: application/json, text/javascript, */*; q=0.01' \
    -H 'Authorization: Bearer YOUR_API_ACCESS_TOKEN'

If the access token has expired, the server will return new `X-Access-Token` for use in other requests (option `sendAccessToken` must be set to `true`).

# Routing

Admin dashboard path by default: http://example.com/admin/api/

Path for access to API endpoint: http://example.com/api/<code>model/module/action</code>
Also see official guideline: https://github.com/yiisoft/yii2/blob/master/docs/guide/rest-quick-start.md

Use the `Module::dashboardNavItems()` method of the module to generate a navigation items list for NavBar, like this:

    <?php
        echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
            'label' => 'Modules',
            'items' => [
                Yii::$app->getModule('api')->dashboardNavItems(),
                ...
            ]
        ]);
    ?>

# Status and version [ready to use]
* v.1.3.12 - Added base API for Menu module, request helper
* v.1.3.11 - Update README.md and dependencies
* v.1.3.10 - Added base API for Comments module
* v.1.3.9 - Update README.md and dependencies
* v.1.3.8 - Added base API for Content module
* v.1.3.7 - Added pagination, up to date dependencies
* v.1.3.6 - Added base API for Media module. Expand API for Blog module.
* v.1.3.5 - Added base API for Blog module
* v.1.3.4 - Added base API for Search module
* v.1.3.3 - Fixed deprecated class declaration