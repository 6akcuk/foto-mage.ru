<?php

Yii::app()->getClientScript()->registerCssFile('/css/forgot.css');
Yii::app()->getClientScript()->registerScriptFile('/js/forgot.js');

?>
<div class="forgot_block">
  <div class="forgot_header">Восстановление доступа к сайту</div>
  <div class="forgot_description">Для восстановления доступа, введите адрес E-Mail</div>

  <div class="forgot_form_wrap">
    <div id="forgot_result"></div>
    <div id="forgot_error" class="error"></div>
    <div style="margin-bottom: 10px">
      <?php echo ActiveHtml::textField('email', '', array('placeholder' => 'Аккаунт (E-Mail адрес)', 'class' => 'text')) ?>
    </div>
    <div id="code_row" style="display: none; margin-bottom: 10px">
      <?php echo ActiveHtml::textField('code', '', array('placeholder' => 'Код восстановления доступа', 'class' => 'text')) ?>
    </div>
    <div class="button_blue button_wide button_big clear_fix">
      <button id="forgot_button" onclick="Forgot.submit()">Отправить запрос</button>
    </div>
  </div>
</div>

<?php
$this->pageJS = <<<HTML
Forgot.init();
HTML;
