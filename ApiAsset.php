<?php

namespace wdmg\api;
use yii\web\AssetBundle;

class ApiAsset extends AssetBundle
{
    public $sourcePath = '@bower/clipboard';

    public function init()
    {
        parent::init();
        $this->js = YII_DEBUG ? ['dist/clipboard.js'] : ['dist/clipboard.min.js'];
    }

}

?>