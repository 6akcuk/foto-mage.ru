<?php

Yii::app()->getClientScript()->registerCssFile('/css/forgot.css');
Yii::app()->getClientScript()->registerScriptFile('/js/forgot.js');

?>
<div class="forgot_block">
  <div class="forgot_header">Восстановление доступа к сайту</div>
  <div class="forgot_description">Введите новый пароль к своему аккаунту</div>

  <div class="forgot_form_wrap">
    <div id="forgot_result"></div>
    <div id="forgot_error" class="error"></div>
    <div style="margin-bottom: 10px">
      <?php echo ActiveHtml::passwordField('new_password', '', array('placeholder' => 'Новый пароль', 'class' => 'text')) ?>
    </div>
    <div style="margin-bottom: 10px">
      <?php echo ActiveHtml::passwordField('new_password_rpt', '', array('placeholder' => 'Повторите пароль', 'class' => 'text')) ?>
    </div>
    <div class="button_blue button_wide button_big clear_fix">
      <button id="forgot_button" onclick="Forgot.restore('<?php echo $email ?>', '<?php echo $code ?>')">Сменить пароль</button>
    </div>
  </div>
</div>
<script>
  function changePwd() {
    FormMgr.submit('#chpwdform', 'right', function(r) {
      if (r.success) location.href = '/';
    });
  }
</script>

<?php
$this->pageJS = <<<HTML
Forgot.initRestore();
HTML;
