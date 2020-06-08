<?php

namespace wdmg\api;
use yii\web\AssetBundle;

class HighLightAsset extends AssetBundle
{

    public $jsOptions = array(
        'position' => \yii\web\View::POS_END
    );

    public function init()
    {
        parent::init();
        $this->css = YII_DEBUG ? [
            '//cdn.jsdelivr.net/gh/highlightjs/cdn-release@10.0.3/build/styles/default.min.css'
        ] : [
            '//cdn.jsdelivr.net/gh/highlightjs/cdn-release@10.0.3/build/styles/default.min.css'
        ];
        $this->js = YII_DEBUG ? [
            '//cdn.jsdelivr.net/gh/highlightjs/cdn-release@10.0.3/build/highlight.js'
        ] : [
            '//cdn.jsdelivr.net/gh/highlightjs/cdn-release@10.0.3/build/highlight.min.js'
        ];
    }

}

?>