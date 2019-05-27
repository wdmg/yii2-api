<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use wdmg\widgets\SelectInput;
/* @var $this yii\web\View */
/* @var $searchModel wdmg\api\models\API */

$this->title = Yii::t('app/modules/api', 'Test API');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="page-header">
    <h1>
        <?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small>
    </h1>
</div>
<div class="api-test">
    <div class="col-xs-12 col-md-4">
        <?php $form = ActiveForm::begin([
            'id' => "testApiForm",
            'enableAjaxValidation' => true
        ]); ?>
        <?= $form->field($model, 'action')->widget(SelectInput::className(), [
            'items' => $apiActions,
            'options' => [
                'class' => 'form-control'
            ]
        ]); ?>
        <?= $form->field($model, 'method')->widget(SelectInput::className(), [
            'items' => $requestMethods,
            'options' => [
                'class' => 'form-control'
            ]
        ]); ?>
        <?= $form->field($model, 'accept')->widget(SelectInput::className(), [
            'items' => $acceptResponses,
            'options' => [
                'class' => 'form-control'
            ]
        ]); ?>
        <?= $form->field($model, 'request')->textInput(['maxlength' => true]) ?>
        <hr/>
        <div class="form-group">
            <?= Html::a(Yii::t('app/modules/api', '&larr; Back to list'), ['api/index'], ['class' => 'btn btn-default pull-left']) ?>&nbsp;
            <?= Html::button(Yii::t('app/modules/api', 'Execute'), [
                'id' => 'sendRequest',
                'class' => 'btn btn-success pull-right'
            ]) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <div class="col-xs-12 col-md-8">
        <label>Response</label>
        <pre id="testApiResponse" style="min-height:320px;"></pre>
    </div>
</div>

<?php $this->registerJs(
    'var $form = $("#testApiForm");
    if ($form.length > 0) {

        $form.find(\'#sendRequest\').on(\'click\', function() {
        
            var requestMethod = \'get\';
            if ($form.find(\'#dynamicmodel-method\').val())
                requestMethod = $form.find(\'#dynamicmodel-method\').val();
            
            var requestURL = window.location.href;
            if ($form.find(\'#dynamicmodel-action\').val())
                requestURL = $form.find(\'#dynamicmodel-action\').val();
                
            if ($form.find(\'#dynamicmodel-request\').val())
                requestURL = requestURL + $form.find(\'#dynamicmodel-request\').val();
            
            var requestAccept = \'json\';
            if ($form.find(\'#dynamicmodel-accept\').val())
                requestAccept = $form.find(\'#dynamicmodel-accept\').val();
                
            $.ajax({
                type: requestMethod,
                url: requestURL,
                dataType: requestAccept,
                complete: function(data) {
                    if(data) {
                        if (requestAccept == \'json\' && !(typeof data === "object")) {
                            var data = $.parseJSON(data);
                        }
                        if (data.status == 200) {
                            if(requestAccept == \'json\' && data.responseJSON) {
                                var jsonText = JSON.stringify(data.responseJSON, null, 2);
                                $(\'#testApiResponse\').text(jsonText);
                            }
                            if(requestAccept == \'xml\' && data.responseXML) {
                                var xmlText = new XMLSerializer().serializeToString(data.responseXML);
                                $(\'#testApiResponse\').text(xmlText);
                            }
                        }
                    }
                    console.log(\'Request complete\');
                },
                error: function(data) {
                    console.log(\'Request error: \' + data);
                }
            });
            return false;
        });

    }', \yii\web\View::POS_READY
); ?>


<?php echo $this->render('../_debug'); ?>
