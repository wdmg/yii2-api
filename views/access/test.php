<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use wdmg\widgets\SelectInput;
use wdmg\api\HighLightAsset;

/* @var $this yii\web\View */
/* @var $searchModel wdmg\api\models\API */

$this->title = Yii::t('app/modules/api', 'Test API');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/api', 'Private access to API`s'), 'url' => ['access/index']];
$this->params['breadcrumbs'][] = $this->title;
HighLightAsset::register($this);

?>
<div class="page-header">
    <h1>
        <?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small>
    </h1>
</div>
<div class="api-access-test">
    <div class="col-xs-12 col-md-4">
        <?php $form = ActiveForm::begin([
            'id' => "testApiForm",
            'enableAjaxValidation' => true
        ]); ?>
        <?= $form->field($model, 'action')->widget(SelectInput::class, [
            'items' => $apiActions,
            'options' => [
                'class' => 'form-control'
            ]
        ])->label(Yii::t('app/modules/api', 'Action') . ":"); ?>
        <?= $form->field($model, 'method')->widget(SelectInput::class, [
            'items' => $requestMethods,
            'options' => [
                'class' => 'form-control'
            ]
        ])->label(Yii::t('app/modules/api', 'Method') . ":"); ?>
        <?= $form->field($model, 'accept')->widget(SelectInput::class, [
            'items' => $acceptResponses,
            'options' => [
                'class' => 'form-control'
            ]
        ])->label(Yii::t('app/modules/api', 'Accept') . ":"); ?>
        <?= $form->field($model, 'request')->textarea(['value' => '?access-token='.$accessToken, 'rows' => 6])
            ->label(Yii::t('app/modules/api', 'Request') . ":"); ?>

        <div id="requestHelper" class="form-group">
            <a href="#" class="btn btn-link btn-sm" data-var="access-token" data-value="<?= $accessToken ?>">?access-token=*</a>
            <a href="#" class="btn btn-link btn-sm" data-var="expand" data-value="created">?expand=created</a>
            <a href="#" class="btn btn-link btn-sm" data-var="expand" data-value="updated">?expand=updated</a>
            <a href="#" class="btn btn-link btn-sm" data-var="expand" data-value="tags">?expand=tags</a>
            <a href="#" class="btn btn-link btn-sm" data-var="expand" data-value="categories">?expand=categories</a>
        </div>
        <hr/>
        <div class="form-group">
            <?= Html::a(Yii::t('app/modules/api', '&larr; Back to list'), ['access/index'], ['class' => 'btn btn-default pull-left']) ?>&nbsp;
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

<?php $this->registerJs(<<< JS
    var _form = $("#testApiForm");
    if (_form.length > 0) {

        var resend = false;
        _form.find('#sendRequest').on('click', function() {
        
            $('#testApiResponse').text('');
        
            var requestMethod = 'get';
            if (_form.find('#dynamicmodel-method').val())
                requestMethod = _form.find('#dynamicmodel-method').val();
            
            var requestURL = window.location.href;
            if (_form.find('#dynamicmodel-action').val())
                requestURL = _form.find('#dynamicmodel-action').val();
                
            if (_form.find('#dynamicmodel-request').val())
                requestURL = requestURL + _form.find('#dynamicmodel-request').val();
            
            var requestAccept = 'json';
            if (_form.find('#dynamicmodel-accept').val())
                requestAccept = _form.find('#dynamicmodel-accept').val();
            
            $('#testApiResponse').removeAttr('class');
            
            let url = new URL(requestURL, window.location.origin);
            $.ajax({
                type: requestMethod,
                url: url.toString(),
                dataType: requestAccept,
                cache: false,
                complete: function(data) {
                    if(data) {
                        if (requestAccept == 'json' && !(typeof data === "object")) {
                            var data = $.parseJSON(data);
                        }
                        if (data.status == 200) {
                            if (requestAccept == 'json' && data.responseJSON) {
                                var json = JSON.stringify(data.responseJSON, null, 2);
                                $('#testApiResponse').html('<code>' + json + '</code>');
                                hljs.initHighlighting.called = false;
                                hljs.highlightBlock($('#testApiResponse').get(0));
                            }
                            if (requestAccept == 'xml' && data.responseXML) {
                                var xml = new XMLSerializer().serializeToString(data.responseXML);
                                $('#testApiResponse').html('<code>' + xml.replace(/[\u00A0-\u9999<>\&]/gim, function(i) {
                                    return '&#'+i.charCodeAt(0)+';';
                                }) + '</code>');
                                hljs.initHighlighting.called = false;
                                hljs.highlightBlock($('#testApiResponse').get(0));
                            }
                        }
                    }
                    console.log('Request complete', data);
                    
                    if (data.status && data.statusText) {
                        var labelClass = 'default';
                        
                        if (data.status == 429) {
                            labelClass = 'warning';
                        } else if (data.status >= 399) {
                            labelClass = 'danger';
                        } else if (data.status >= 299) {
                            labelClass = 'info';
                        } else if (data.status >= 199) {
                            labelClass = 'success';
                        }
                        $('#testApiStatus').html('<span class="label label-' + labelClass + '">' + data.status + '</span>' + '&nbsp;' + '<span class="text-' + labelClass + '">' + data.statusText + '</span>');
                    }
                    
                    if (data.getResponseHeader('X-Access-Token') && !resend) {
                        var accessToken = '?access-token=' + data.getResponseHeader('X-Access-Token');
                        _form.find('#dynamicmodel-request').val(accessToken);
                        console.log('Set new access token', accessToken);
                        _form.find('#sendRequest').click();
                        resend = true;
                    }
                },
                error: function(data) {
                    console.log('Request error', data);
                }
            });
            return false;
        });
        _form.find('#requestHelper a').on('click', function(event) {
            event.preventDefault();
            let target = $(event.target);
            
            let data = target.data();
            if (data.var && data.value) {
                let request = $('#dynamicmodel-request').val();
                let params = new URLSearchParams(request);
                if (params.has(data.var) && data.var !== "access-token") {
                    params.append(data.var, data.value);
                    let all = params.get(data.var).split(',');
                    if (all.includes(data.value)) {
                        let indx = all.indexOf(data.value);
                        if (indx != -1) {
                            all.splice(indx, 1);
                        }
                    } else {
                        all.push(data.value);
                    }
                        
                    params.set(data.var, all.join(','));
                } else {
                    params.set(data.var, data.value);
                }
                
                $('#dynamicmodel-request').val('?'+params.toString().replaceAll(['%2C'], [',']));
            }
            
            target.toggleClass('btn-link btn-primary');
        });
    }
JS
, \yii\web\View::POS_READY); ?>

<?php echo $this->render('../_debug'); ?>
