<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Tabs;
use wdmg\api\ApiAsset;

use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel wdmg\api\models\APISearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $this->context->module->name;
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['api/index']];
$this->params['breadcrumbs'][] = Yii::t('app/modules/api', 'List of available API`s');
ApiAsset::register($this);

$this->registerJs(<<< JS

    /* To initialize BS3 tooltips set this below */
    $(function () {
        $("[data-toggle='tooltip']").tooltip(); 
    });
    
    /* To initialize BS3 popovers set this below */
    /*$(function () {
        $("[data-toggle='popover']").popover(); 
    });*/

JS
);

?>
<div class="page-header">
    <h1>
        <?= Html::encode(Yii::t('app/modules/api', 'List of available API`s')) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small>
    </h1>
</div>
<div class="api-index">
    <?= Tabs::widget([
        'items' => [
            [
                'label' => Yii::t('app/modules/api', 'Public API`s'),
                'content' => $this->render('list/_public', ['mode' => $modes['public'], 'model' => $dataProvider['public']]),
                'active' => true
            ], [
                'label' => Yii::t('app/modules/api', 'Private API`s'),
                'content' => $this->render('list/_private', ['mode' => $modes['private'], 'model' => $dataProvider['private']]),
            ]
        ]
    ]); ?>
</div>

<?php echo $this->render('../_debug'); ?>
