<?php

namespace wdmg\api;
use yii\web\AssetBundle;

class ApiAsset extends AssetBundle
{
    public $sourcePath = '@bower/clipboard/dist';


    public $jsOptions = array(
        'position' => \yii\web\View::POS_END
    );

    public function init()
    {
        parent::init();
        $this->js = YII_DEBUG ? ['clipboard.js'] : ['clipboard.min.js'];
    }

}

?>