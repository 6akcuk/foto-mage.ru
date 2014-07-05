<?php
/** @var Support $support */

Yii::app()->getClientScript()->registerCssFile('/css/support.css');

?>

<div class="support_form_content">
  <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
    'id' => 'post_form'
  )); ?>
  <div id="support_result"<?php if ($success_msg) echo ' style="display: block"' ?>><?php echo $success_msg ?></div>
  <div id="support_error" class="error"<?php if (sizeof($support->getErrors()) > 0) echo ' style="display: block"' ?>>
  <?php $errors = $support->getErrors(); ?>
  <?php foreach ($errors as $error): ?>
    <?php echo (is_array($error)) ? implode('<br/>', $error) : $error ?><br/>
  <?php endforeach; ?>
  </div>
  <div class="support_form_param">
    <div class="support_form_header"><?php echo $support->getAttributeLabel('name') ?></div>
    <?php echo ActiveHtml::textField('name', $support->name, array('class' => 'text')) ?>
  </div>
  <div class="support_form_param">
    <div class="support_form_header"><?php echo $support->getAttributeLabel('email') ?></div>
    <?php echo ActiveHtml::textField('email', $support->email, array('class' => 'text')) ?>
  </div>
  <div class="support_form_param">
    <div class="support_form_header"><?php echo $support->getAttributeLabel('msg') ?></div>
    <?php echo ActiveHtml::textArea('msg', $support->msg, array('class' => 'text', 'onkeyup' => 'checkTextLength(4096, this, \'#support_warn\')')) ?>
    <div id="support_warn" class="support_warn"></div>
  </div>
  <div class="support_form_param clear_fix">
    <div class="fl_r button_blue"><button>Отправить</button></div>
  </div>
  <?php $this->endWidget(); ?>
</div>

<script type="text/javascript">
autosizeSetup('#msg', {minHeight: 64, maxHeight: 160, exact: true});
</script>