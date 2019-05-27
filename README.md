[![Progress](https://img.shields.io/badge/required-Yii2_v2.0.13-blue.svg)](https://packagist.org/packages/yiisoft/yii2)
[![Github all releases](https://img.shields.io/github/downloads/wdmg/yii2-api/total.svg)](https://GitHub.com/wdmg/yii2-api/releases/)
[![GitHub version](https://badge.fury.io/gh/wdmg/yii2-api.svg)](https://github.com/wdmg/yii2-api)
![Progress](https://img.shields.io/badge/progress-in_development-red.svg)
[![GitHub license](https://img.shields.io/github/license/wdmg/yii2-api.svg)](https://github.com/wdmg/yii2-api/blob/master/LICENSE)

# Yii2 API
API control module

# Requirements 
* PHP 5.6 or higher
* Yii2 v.2.0.13 and newest
* [Yii2 SelectInput](https://github.com/wdmg/yii2-selectinput) widget
* [Yii2 Users](https://github.com/wdmg/yii2-users) module (required)

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
            'routePrefix' => 'admin',
            'accessTokenExpire', => 3600 // lifetime of `access_token`, `0` - unlimited
            'blockedIp' => [], // blocked access from IP`s
            'rateLimit' => 30 // request`s to API per minute
        ],
        ...
    ],

If you have connected the module not via a composer add Bootstrap section:

`
$config['bootstrap'][] = 'wdmg\api\Bootstrap';
`

# Routing
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

# Status and version [in progress development]
* v.1.0.1 - Added check access by IP
* v.1.0.0 - Added rate limit for API-requests, auth by access-token, separate controllers and models
* v.0.0.3 - Added routing and base auth