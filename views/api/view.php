<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model wdmg\api\models\API */

\yii\web\YiiAsset::register($this);

?>
<div class="api-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'user_id',
            'user_ip',
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
            'created_at:datetime',
            'updated_at:datetime',
            'allowance_at:datetime',
            [
                'attribute' => 'status',
                'format' => 'html',
                'value' => function($data) use ($model) {
                    if ($model->statusModesList) {
                        if($data->status)
                            return '<span class="label label-success">' . $model->statusModesList[$data->status] . '</span>';
                        else
                            return '<span class="label label-danger">' . $model->statusModesList[$data->status] . '</span>';
                    } else {
                        return $data->type;
                    }
                },
            ]
        ],
    ]) ?>
    <div class="modal-footer">
        <?= Html::a(Yii::t('app/modules/options', 'Close'), "#", [
            'class' => 'btn btn-default pull-left',
            'data-dismiss' => 'modal'
        ]); ?>
        <?= Html::a(Yii::t('app/modules/options', 'Edit'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary pull-right']); ?>
        <?= Html::a(Yii::t('app/modules/options', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger pull-right',
            'data' => [
                'confirm' => Yii::t('app/modules/options', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]); ?>
    </div>
</div>
