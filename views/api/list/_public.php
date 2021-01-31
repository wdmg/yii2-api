<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

$disabled = '';
if ($mode == false)
    $disabled = 'disabled';

?>

<div class="api-public-list">
    <?php Pjax::begin([
        'id' => "apiPublicListAjax",
        'timeout' => 5000
    ]); ?>
    <?= GridView::widget([
        'dataProvider' => $model,
        'layout' => '{summary}<br\/>{items}<br\/>{summary}<br\/><div class="text-center">{pager}</div>',
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn'
            ],
            [
                'attribute' => 'class',
                'format' => 'text',
                'label' => Yii::t('app/modules/api', 'Class'),
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'label' => Yii::t('app/modules/api', 'Status'),
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'value' => function($data) use ($disabled) {
                    if ($this->context->module->moduleLoaded('options')) {
                        if ($data['status']) {
                            return '<div id="switcher-' . intval($data['id']) . '" data-value-current="' . intval($data['status']) . '" data-id="' . intval($data['id']) . '" data-toggle="button-switcher" class="btn-group btn-toggle"><button data-value="0" '.$disabled.' class="btn btn-xs btn-default">OFF</button><button data-value="1" '.$disabled.' class="btn btn-xs btn-primary">ON</button></div>';
                        } else {
                            return '<div id="switcher-' . intval($data['id']) . '" data-value-current="' . intval($data['status']) . '" data-id="' . intval($data['id']) . '" data-toggle="button-switcher" class="btn-group btn-toggle"><button data-value="0" '.$disabled.' class="btn btn-xs btn-danger">OFF</button><button data-value="1" '.$disabled.' class="btn btn-xs btn-default">ON</button></div>';
                        }
                    } else {
                        if (boolval($data['status']))
                            return '<span class="label label-success" '.$disabled.'>' . Yii::t('app/modules/api', 'Active') . '</span>';
                        else
                            return '<span class="label label-danger" '.$disabled.'>' . Yii::t('app/modules/api', 'Disabled') . '</span>';
                    }
                }
            ]
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
<?php $this->registerJs(
    'var $container = $("#apiPublicListAjax");
    var requestURL = window.location.href;
    if ($container.length > 0) {
        $container.delegate(\'[data-toggle="button-switcher"] button\', \'click\', function() {
            let id = $(this).parent(\'.btn-group\').data(\'id\');
            let value = $(this).data(\'value\');
            let url = new URL(requestURL);
            url.searchParams.set(\'change\', \'status\');            
            $.ajax({
                type: "POST",
                url: url.toString(),
                dataType: \'json\',
                data: {\'id\': id, \'value\': value, \'mode\': \'public\'},
                complete: function(data) {
                    $.pjax.reload({type:\'POST\', container:\'#apiPublicListAjax\'});
                }
            });
        });
    };', \yii\web\View::POS_READY
); ?>