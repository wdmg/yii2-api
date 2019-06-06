<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use wdmg\api\MainAsset;
use wdmg\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel wdmg\api\models\APISearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $this->context->module->name;
$this->params['breadcrumbs'][] = $this->title;
$bundle = MainAsset::register($this);

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
        <?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small>
    </h1>
</div>
<div class="api-index">
    <?php Pjax::begin([
        'id' => "apiClientsAjax",
        'timeout' => 5000
    ]); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => '{summary}<br\/>{items}<br\/>{summary}<br\/><div class="text-center">{pager}</div>',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            [
                'attribute' => 'user_id',
                'format' => 'html',
                'header' => Yii::t('app/modules/api', 'User'),
                'value' => function($model) {
                    if($model->user_id == $model->user['id'])
                        if($model->user['id'] && $model->user['username'])
                            return Html::a($model->user['username'], ['../admin/users/view/?id='.$model->user['id']], [
                                'target' => '_blank',
                                'data-pjax' => 0
                            ]);
                        else
                            return $model->user_id;
                    else
                        return $model->user_id;
                }
            ],
            [
                'attribute' => 'user_ip',
                'format' => 'raw',
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'value' => function($data) {

                    if (!$data->user_ip)
                        return '<em class="text-danger">('.Yii::t('app/modules/api', 'from any IP').')</em>';
                    else
                        return $data->user_ip;
                }
            ],
            [
                'attribute' => 'access_token',
                'format' => 'raw',
                'value' => function($data) {
                    return Html::a($data->access_token, '#', [
                        'title' => Yii::t('app/modules/api', 'Copy to clipboard'),
                        'data-clipboard-text' => $data->access_token,
                        'data-toggle' => 'tooltip',
                        'data-pjax' => '0'
                    ]);
                }
            ],
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
                    if ($data->status == $data::API_CLIENT_STATUS_ACTIVE) {
                        return '<div id="switcher-' . $data->id . '" data-value-current="' . $data->status . '" data-id="' . $data->id . '" data-toggle="button-switcher" class="btn-group btn-toggle"><button data-value="0" class="btn btn-xs btn-default">OFF</button><button data-value="1" class="btn btn-xs btn-primary">ON</button></div>';
                    } else {
                        return '<div id="switcher-' . $data->id . '" data-value-current="' . $data->status . '" data-id="' . $data->id . '" data-toggle="button-switcher" class="btn-group btn-toggle"><button data-value="0" class="btn btn-xs btn-danger">OFF</button><button data-value="1" class="btn btn-xs btn-default">ON</button></div>';
                    }
                }
            ],
            [
                'attribute' => 'updated_at',
                'format' => 'datetime',
                'filter' => DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'updated_at',
                    'options' => [
                        'class' => 'form-control'/*,
                        'value' => date('Y-m-s H:i:s')*/
                    ],
                    'pluginOptions' => [
                        'className' => '.datepicker',
                        'input' => '.form-control',
                        'format' => 'YYYY-MM-DD HH:mm:ss',
                        'toggle' => '.input-group-btn > button',
                    ]
                ]),
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'value' => function($data) {
                    return $data->updated_at;
                },
            ],
            [
                'attribute' => 'allowance_at',
                'format' => 'datetime',
                'filter' => DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'allowance_at',
                    'options' => [
                        'class' => 'form-control'/*,
                        'value' => date('Y-m-s H:i:s')*/
                    ],
                    'pluginOptions' => [
                        'className' => '.datepicker',
                        'input' => '.form-control',
                        'format' => 'YYYY-MM-DD HH:mm:ss',
                        'toggle' => '.input-group-btn > button',
                    ]
                ]),
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'value' => function($data) {
                    return $data->allowance_at;
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => Yii::t('app/modules/api', 'Actions'),
                'contentOptions' => [
                    'class' => 'text-center',
                    'style' => 'min-width:96px;',
                ],
                'buttons'=> [
                    'view' => function($url, $data, $key) {
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', Url::to(['api/view', 'id' => $data['id']]), [
                            'class' => 'api-details-link',
                            'title' => Yii::t('yii', 'View'),
                            'data-toggle' => 'modal',
                            'data-target' => '#apiDetails',
                            'data-id' => $key,
                            'data-pjax' => '1'
                        ]);
                    },
                    'refresh' => function($url, $data, $key) {
                        return Html::a('<span class="glyphicon glyphicon-refresh"></span>', false, [
                            'title' => Yii::t('app/modules/api', 'Renew access-token'),
                            'data-toggle' => 'renew-access-token',
                            'data-id' => $key,
                            'data-pjax' => '0'
                        ]);
                    },
                    'play' => function($url, $data, $key) {
                        return Html::a('Test <span class="glyphicon glyphicon-play"></span>', Url::to(['api/test', 'access-token' => $data['access_token']]), [
                            'title' => Yii::t('app/modules/api', 'Test API with this access-token'),
                            'class' => 'text-success',
                            'data-id' => $key,
                            'data-pjax' => '0'
                        ]);
                    },
                ],
                'template' => '{view}&nbsp;{update}&nbsp;{delete}&nbsp;{refresh}&nbsp;{play}'
            ]
        ],
    ]); ?>
    <hr/>
    <div>
        <?= Html::a(Yii::t('app/modules/api', 'Testing API'), ['test'], ['class' => 'btn btn-info pull-left']) ?>
        <?= Html::a(Yii::t('app/modules/api', 'Add new client'), ['create'], ['class' => 'btn btn-success pull-right']) ?>
    </div>
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
    
    var $container = $("#apiClientsAjax");
    var requestURL = window.location.href;
    if ($container.length > 0) {
        $container.delegate(\'[data-toggle="renew-access-token"]\', \'click\', function() {
            var id = $(this).data(\'id\');
             $.ajax({
                type: "POST",
                url: requestURL + \'?change=access-token\',
                dataType: \'json\',
                data: {\'id\': id},
                complete: function(data) {
                    $.pjax.reload({type:\'POST\', container:\'#apiClientsAjax\'});
                }
             });
        });
        $container.delegate(\'[data-toggle="button-switcher"] button\', \'click\', function() {
            var id = $(this).parent(\'.btn-group\').data(\'id\');
            var value = $(this).data(\'value\');
             $.ajax({
                type: "POST",
                url: requestURL + \'?change=status\',
                dataType: \'json\',
                data: {\'id\': id, \'value\': value},
                complete: function(data) {
                    $.pjax.reload({type:\'POST\', container:\'#apiClientsAjax\'});
                }
             });
        });
    }', \yii\web\View::POS_READY
); ?>
<?php $this->registerJs(<<< JS
$('body').delegate('.api-details-link', 'click', function(event) {
    event.preventDefault();
    $.get(
        $(this).attr('href'),
        function (data) {
            var body = $(data).remove('.modal-footer').html();
            var footer = $(data).find('.modal-footer').html();
            $('#apiDetails .modal-body').html(body);
            $('#apiDetails .modal-body').find('.modal-footer').remove();
            $('#apiDetails .modal-footer').html(footer);
            $('#apiDetails').modal();
        }  
    );
});
JS
); ?>
<?php Modal::begin([
    'id' => 'apiDetails',
    'header' => '<h4 class="modal-title">'.Yii::t('app/modules/api', 'Client details').'</h4>',
    'footer' => '<a href="#" class="btn btn-default pull-left" data-dismiss="modal">'.Yii::t('app/modules/api', 'Close').'</a>',
    'clientOptions' => [
        'show' => false
    ]
]); ?>
<?php Modal::end(); ?>

<?php echo $this->render('../_debug'); ?>
