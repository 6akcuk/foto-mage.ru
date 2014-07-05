<?php
/** @var OperationForm $model
 * @var ActiveForm $form
 */

$this->pageTitle = 'Редактирование операции';
?>
<div class="role_create_content">
  <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
    'id' => 'editOperationForm'
  )); ?>
  <div id="op_edit_result"></div>
  <div id="op_edit_error" class="error"></div>
  <div class="role_create_header"><?php echo $model->getAttributeLabel('name') ?></div>
  <?php echo $form->textField($model, 'name', array('class' => 'text')) ?>
  <div class="role_create_header"><?php echo $model->getAttributeLabel('bizrule') ?></div>
  <?php echo $form->textField($model, 'bizrule', array('class' => 'text')) ?>
  <div class="role_create_header"><?php echo $model->getAttributeLabel('description') ?></div>
  <?php echo $form->textArea($model, 'description', array('class' => 'text', 'onkeyup' => 'checkTextLength(256, this, \'#op_create_warn\')')) ?>
  <div id="op_edit_warn" class="role_create_warn"></div>
  <?php $this->endWidget(); ?>
</div>
<?php
$this->pageJS = <<<HTML
autosizeSetup('#OperationForm_description', {minHeight: 32, maxHeight: 96, exact: true});
HTML;
