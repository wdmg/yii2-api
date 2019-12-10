<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use wdmg\widgets\SelectInput;

/* @var $this yii\web\View */
/* @var $model wdmg\api\models\API */
/* @var $form yii\widgets\ActiveForm */

$statusModes = $model->statusModesList;

?>
<div class="api-access-form">
    <?php $form = ActiveForm::begin(); ?>
    <?php if ($model->id) {
        echo $form->field($model, 'user_id')->textInput(['readonly' => "readonly", 'disabbled' => "disabbled"]);
        echo '<label>' . $model->getAttributeLabel('access_token') . '</label>&nbsp;';
        echo Html::a($model->access_token, '#', [
            'title' => Yii::t('app/modules/api', 'Copy to clipboard'),
            'data-clipboard-text' => $model->access_token,
            'data-toggle' => 'tooltip',
            'data-pjax' => '0'
        ]);
        echo '&nbsp;' . Html::a('<span class="glyphicon glyphicon-refresh"></span>', Url::to(['access/update', 'id' => $model->id, 'change' => 'access-token']), [
            'title' => Yii::t('app/modules/api', 'Renew access-token'),
            'data-toggle' => 'renew-access-token',
            'data-pjax' => '0'
        ]);
    } else {
        echo $form->field($model, 'user_id')->textInput();
    } ?>
    <?= $form->field($model, 'user_ip')->textarea(['rows' => 3]) ?>
    <?= $form->field($model, 'status')->widget(SelectInput::class, [
        'items' => $statusModes,
        'options' => [
            'class' => 'form-control'
        ]
    ]); ?>

    <hr/>
    <div class="form-group">
        <?= Html::a(Yii::t('app/modules/api', '&larr; Back to list'), ['access/index'], ['class' => 'btn btn-default pull-left']) ?>&nbsp;
        <?= Html::submitButton(Yii::t('app/modules/api', 'Save'), ['class' => 'btn btn-success pull-right']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>