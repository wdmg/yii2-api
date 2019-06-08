<?php

use yii\helpers\Html;
use wdmg\api\ApiAsset;

/* @var $this yii\web\View */
/* @var $model wdmg\api\models\API */

$this->title = Yii::t('app/modules/api', 'Create client');
$this->params['breadcrumbs'][] = ['label' => $this->context->module->name, 'url' => ['api/index']];
$this->params['breadcrumbs'][] = $this->title;
$bundle = ApiAsset::register($this);

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
    <h1><?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small></h1>
</div>
<div class="api-create">
    <?= $this->render('_form', [
        'model' => $model
    ]) ?>
</div>

<?php echo $this->render('../_debug'); ?>
