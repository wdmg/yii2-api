<?php

namespace wdmg\api;

/**
 * Yii2 API
 *
 * @category        Module
 * @version         1.0.2
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-api
 * @copyright       Copyright (c) 2019 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use Yii;

/**
 * api module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'wdmg\api\controllers';

    /**
     * {@inheritdoc}
     */
    public $defaultRoute = 'default';

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
    public $description = "API control module for Yii2";

    /**
     * @var string the vendor name of module
     */
    private $vendor = "wdmg";

    /**
     * @var string the module version
     */
    private $version = "1.0.2";

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
}