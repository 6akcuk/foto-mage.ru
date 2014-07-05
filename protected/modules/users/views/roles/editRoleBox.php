<?php
/** @var RoleForm $model
 * @var ActiveForm $form
 */

$this->pageTitle = 'Редактирование роли';
?>
<div class="role_create_content">
  <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
    'id' => 'editRoleForm'
  )); ?>
  <div id="re_result"></div>
  <div id="re_error" class="error"></div>
  <div class="role_create_header"><?php echo $model->getAttributeLabel('name') ?></div>
  <?php echo $form->textField($model, 'name', array('class' => 'text')) ?>
  <div id="RoleForm_name_warn" class="role_create_warn"></div>
  <div class="role_create_header"><?php echo $model->getAttributeLabel('bizrule') ?></div>
  <?php echo $form->textField($model, 'bizrule', array('class' => 'text')) ?>
  <div id="RoleForm_bizrule_warn" class="role_create_warn"></div>
  <div class="role_create_header"><?php echo $model->getAttributeLabel('description') ?></div>
  <?php echo $form->textArea($model, 'description', array('class' => 'text', 'onkeyup' => 'checkTextLength(256, this, \'#re_warn\')')) ?>
  <div id="re_warn" class="role_create_warn"></div>
  <?php $this->endWidget(); ?>
</div>
<?php
$this->pageJS = <<<HTML
autosizeSetup('#RoleForm_description', {minHeight: 32, maxHeight: 96, exact: true});
HTML;
