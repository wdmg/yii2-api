<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

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
            'class',
            [
                'attribute' => 'status',
                'format' => 'raw',
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'value' => function($data) {
                    if ($data['status']) {
                        return '<div id="switcher-' . intval($data['id']) . '" data-value-current="' . intval($data['status']) . '" data-id="' . intval($data['id']) . '" data-toggle="button-switcher" class="btn-group btn-toggle"><button data-value="0" class="btn btn-xs btn-default">OFF</button><button data-value="1" class="btn btn-xs btn-primary">ON</button></div>';
                    } else {
                        return '<div id="switcher-' . intval($data['id']) . '" data-value-current="' . intval($data['status']) . '" data-id="' . intval($data['id']) . '" data-toggle="button-switcher" class="btn-group btn-toggle"><button data-value="0" class="btn btn-xs btn-danger">OFF</button><button data-value="1" class="btn btn-xs btn-default">ON</button></div>';
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
            var id = $(this).parent(\'.btn-group\').data(\'id\');
            var value = $(this).data(\'value\');
             $.ajax({
                type: "POST",
                url: requestURL + \'?change=status\',
                dataType: \'json\',
                data: {\'id\': id, \'value\': value, \'mode\': \'public\'},
                complete: function(data) {
                    $.pjax.reload({type:\'POST\', container:\'#apiPublicListAjax\'});
                }
             });
        });
    };', \yii\web\View::POS_READY
); ?>