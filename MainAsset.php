<?php

namespace wdmg\api;
use yii\web\AssetBundle;

class MainAsset extends AssetBundle
{
    public $sourcePath = '@bower/clipboard';

    public function init()
    {
        parent::init();
        $this->js = YII_DEBUG ? ['dist/clipboard.js'] : ['dist/clipboard.min.js'];
        $this->depends = [\yii\web\JqueryAsset::className()];
    }

}

?>