<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use wdmg\widgets\SelectInput;
/* @var $this yii\web\View */
/* @var $searchModel wdmg\api\models\API */

$this->title = Yii::t('app/modules/api', 'Test API');
$this->params['breadcrumbs'][] = ['label' => $this->context->module->name, 'url' => ['api/index']];
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
        ])->label(Yii::t('app/modules/api', 'Action') . ":"); ?>
        <?= $form->field($model, 'method')->widget(SelectInput::className(), [
            'items' => $requestMethods,
            'options' => [
                'class' => 'form-control'
            ]
        ])->label(Yii::t('app/modules/api', 'Method') . ":"); ?>
        <?= $form->field($model, 'accept')->widget(SelectInput::className(), [
            'items' => $acceptResponses,
            'options' => [
                'class' => 'form-control'
            ]
        ])->label(Yii::t('app/modules/api', 'Accept') . ":"); ?>
        <?= $form->field($model, 'request')->textarea(['value' => '?access-token='.$accessToken, 'rows' => 6])
            ->label(Yii::t('app/modules/api', 'Request') . ":"); ?>
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
        <label><?= Yii::t('app/modules/api', 'Response') . ":"; ?></label>
        <span id="testApiStatus"></span>
        <pre id="testApiResponse" style="min-height:320px;"></pre>
    </div>
</div>

<?php $this->registerJs(
    'var $form = $("#testApiForm");
    if ($form.length > 0) {

        $form.find(\'#sendRequest\').on(\'click\', function() {
        
            var _this = $(this);
            $(\'#testApiResponse\').text(\'\');
        
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
                        /*if (data.status && data.response.message) {
                            $(\'#testApiStatus\').html(data.response.status +\' \'+ data.response.message);
                        }*/
                    }
                    console.log(\'Request complete\', data);
                    
                    if (data.status && data.statusText) {
                        var labelClass = \'default\';
                        
                        if (data.status == 429) {
                            labelClass = \'warning\';
                        } else if (data.status >= 399) {
                            labelClass = \'danger\';
                        } else if (data.status >= 299) {
                            labelClass = \'info\';
                        } else if (data.status >= 199) {
                            labelClass = \'success\';
                        }
                        $(\'#testApiStatus\').html(\'<span class="label label-\' + labelClass + \'">\' + data.status + \'</span>\' + \'&nbsp;\' + \'<span class="text-\' + labelClass + \'">\' + data.statusText + \'</span>\');
                    }
                    
                    if (data.getResponseHeader(\'X-Access-Token\')) {
                        var accessToken = \'?access-token=\' + data.getResponseHeader(\'X-Access-Token\');
                        $form.find(\'#dynamicmodel-request\').val(accessToken);
                        console.log(\'Set new access token\', accessToken);
                        $form.find(\'#sendRequest\').click();
                    }
                },
                error: function(data) {
                    console.log(\'Request error\', data);
                }
            });
            return false;
        });

    }', \yii\web\View::POS_READY
); ?>


<?php echo $this->render('../_debug'); ?>
