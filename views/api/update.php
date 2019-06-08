<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use wdmg\api\ApiAsset;

/* @var $this yii\web\View */
/* @var $model wdmg\api\models\API */

$this->title = Yii::t('app/modules/api', 'Update client: {name}', [
    'name' => $model->username,
]);
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
<div class="api-update">
    <?php Pjax::begin([
        'id' => "apiFormClientAjax",
        'timeout' => 5000
    ]); ?>
    <?= $this->render('_form', [
        'model' => $model
    ]) ?>
    <?php Pjax::end(); ?>
</div>

<?php $this->registerJs(
'var clipboard = new ClipboardJS(\'[data-clipboard-text]\');
    clipboard.on(\'success\', function(e) {
        var target = $(e.trigger);
        var title_origin = target.attr(\'data-original-title\');
        target.attr(\'data-original-title\', \'Copied!\').tooltip(\'show\');
        target.attr(\'data-original-title\', title_origin).tooltip(\'fixTitle\');
        e.clearSelection();
    });
    
    var $container = $("#apiFormClientAjax");
    if ($container.length > 0) {
        $container.delegate(\'[data-toggle="renew-access-token"]\', \'click\', function(event) {
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: $(this).attr(\'href\'),
                dataType: \'json\',
                complete: function(data) {
                    $.pjax.reload({container:\'#apiFormClientAjax\'});
                }
            });
        });
    }', \yii\web\View::POS_READY
); ?>

<?php echo $this->render('../_debug'); ?>
