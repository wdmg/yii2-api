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
	<?php $form = ActiveForm::begin([
		'id' => "testApiForm",
		'enableAjaxValidation' => true
	]); ?>
    <div class="col-xs-12 col-md-4">

        <?= $form->field($model, 'action')->widget(SelectInput::class, [
            'items' => $apiActions,
            'options' => [
                'class' => 'form-control'
            ]
        ])->label(Yii::t('app/modules/api', 'Action') . ":"); ?>

        <?= $form->field($model, 'auth')->widget(SelectInput::class, [
            'items' => $authMethods,
            'options' => [
                'class' => 'form-control'
            ]
        ])->label(Yii::t('app/modules/api', 'Auth') . ":"); ?>

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

        <?= $form->field($model, 'token')->textInput([
                'value' => $accessToken
        ])->label(Yii::t('app/modules/api', 'Access Token') . ":"); ?>

        <?= $form->field($model, 'request')->textarea([
                'value' => '?access-token='.$accessToken,
                'rows' => 6
        ])->label(Yii::t('app/modules/api', 'Request') . ":"); ?>

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
    </div>
    <div class="col-xs-12 col-md-8">
        <label><?= Yii::t('app/modules/api', 'Response') . ":"; ?></label>
        <span id="testApiStatus"></span>
        <pre id="testApiResponse" style="min-height:320px;"></pre>

	    <?= $form->field($model, 'curl')->textarea([
		    'readonly' => true,
		    'rows' => 6
	    ])->label(Yii::t('app/modules/api', 'RAW') . ":"); ?>
    </div>
	<?php ActiveForm::end(); ?>
</div>

<?php $this->registerJs(<<< JS
    var _form = $("#testApiForm");
    if (_form.length > 0) {

        var resend = false;
        
        function authMethodChange(method) {
            if (method == 'paramAuth') {
                _form.find('#requestHelper').show();
                _form.find('#dynamicmodel-request').val('?access-token=');
                _form.find('.field-dynamicmodel-token').hide();
            } else {
                _form.find('#requestHelper').hide();
                _form.find('#dynamicmodel-request').val('');
                _form.find('.field-dynamicmodel-token').show();
            }
        }
        
        _form.find('#dynamicmodel-auth').on('change', function (event) {
            authMethodChange($(event.target).val());
        });
        authMethodChange(_form.find('#dynamicmodel-auth').val());
        
        _form.find('#sendRequest').on('click', function() {
        
            $('#testApiStatus').text('');
            $('#testApiResponse').text('');
        
            var requestMethod = 'get';
            if (_form.find('#dynamicmodel-method').val())
                requestMethod = _form.find('#dynamicmodel-method').val();
        
            var authMethod = 'basicAuth';
            if (_form.find('#dynamicmodel-auth').val())
                authMethod = _form.find('#dynamicmodel-auth').val();
            
            var requestURL = window.location.href;
            if (_form.find('#dynamicmodel-action').val())
                requestURL = _form.find('#dynamicmodel-action').val();
                
            if (_form.find('#dynamicmodel-request').val())
                requestURL = requestURL + _form.find('#dynamicmodel-request').val();
            
            var requestAccept = 'json';
            if (_form.find('#dynamicmodel-accept').val())
                requestAccept = _form.find('#dynamicmodel-accept').val();
            
            let authToken = _form.find('#dynamicmodel-token').val();
                
            $('#testApiResponse').removeAttr('class');
            
            let url = new URL(requestURL, window.location.origin);
            
            let curlHeaders = [];
            let curlRequest = [];
            curlRequest.push("curl '" + url.toString() + "'");
            
            if (requestMethod == 'get')
                curlRequest.push("-X 'GET'");
            else if (requestMethod == 'post')
                curlRequest.push("-X 'POST'");
            else if (requestMethod == 'head')
                curlRequest.push("-X 'HEAD'");
            else if (requestMethod == 'patch')
                curlRequest.push("-X 'PATCH'");
            else if (requestMethod == 'put')
                curlRequest.push("-X 'PUT'");
            else if (requestMethod == 'delete')
                curlRequest.push("-X 'DELETE'");
            else if (requestMethod == 'options')
                curlRequest.push("-X 'OPTIONS'");
            
            if (requestAccept == 'json') {
                curlHeaders.push({'Accept': "application/json"});
                curlRequest.push("-H 'Accept: application/json'");
            } else if (requestAccept = 'xml') {
                curlHeaders.push({'Accept': "application/xml"});
                curlRequest.push("-H 'Accept: application/xml'");
            }

            curlHeaders.push({'Sec-Fetch-Site': "same-origin"});
            curlRequest.push("-H 'Sec-Fetch-Site: same-origin'");
            
            curlHeaders.push({'Accept-Language': "ru"});
            curlRequest.push("-H 'Accept-Language: ru'");
            
            curlHeaders.push({'Accept-Encoding': "gzip, deflate"});
            curlRequest.push("-H 'Accept-Encoding: gzip, deflate'");
            
            curlHeaders.push({'Sec-Fetch-Mode': "cors"});
            curlRequest.push("-H 'Sec-Fetch-Mode: cors'");
            
            curlHeaders.push({'Host': url.host});
            curlRequest.push("-H 'Host: " + url.host + "'");
            
            curlHeaders.push({'Connection': "keep-alive"});
            curlRequest.push("-H 'Connection: keep-alive'");
            
            if (authToken.length) {
                if (authMethod == 'basicAuth') {
                    curlHeaders.push({'Authorization': 'Basic' + authToken});
                    curlRequest.push("-H 'Authorization: Basic " +authToken+ "'");
                } else if (authMethod = 'bearerAuth') {
                    curlHeaders.push({'Authorization': 'Bearer' + authToken});
                    curlRequest.push("-H 'Authorization: Bearer " + authToken + "'");
                }
            }
            
            if (curlRequest.length) {
                _form.find('#dynamicmodel-curl').val(curlRequest.join(" \\\" + "\\r\\n"));
            }
            
            let headers = {};
            if (curlHeaders.length) {
                curlHeaders.forEach(header => {
                    headers = {...headers, ...header};
                });
            }
            
            $.ajax({
                type: requestMethod,
                url: url.toString(),
                dataType: requestAccept,
                cache: false,
                headers: headers,
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
                        
                        let messageText = '';
                        if (data.responseJSON.message) {
                            messageText = data.responseJSON.message;
                        }
                        
                        $('#testApiStatus').html('<span class="label label-' + labelClass + '">' + data.status + '</span>' + '&nbsp;' + '<span class="text-' + labelClass + '">' + data.statusText + ' / ' + messageText + '</span>');
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
                    $('#testApiResponse').html('<code>' + data.responseText + '</code>');
                    hljs.highlightBlock($('#testApiResponse').get(0));
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
