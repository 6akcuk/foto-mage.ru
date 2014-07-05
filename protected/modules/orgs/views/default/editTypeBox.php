<?php
/** @var OrganizationType $type
 * @var ActiveForm $form
 */

$this->pageTitle = 'Редактирование типа организации';
?>
  <div class="org_type_content">
    <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
      'id' => 'type_form'
    )); ?>
    <div id="org_type_result"></div>
    <div id="org_type_error" class="error"></div>
    <div class="org_type_header"><?php echo $type->getAttributeLabel('type_name') ?></div>
    <?php echo ActiveHtml::textField('type_name', $type->type_name, array('class' => 'text')) ?>
    <?php echo ActiveHtml::hiddenField('afisha', $type->afisha, array('class' => 'text')) ?>
    <?php $this->endWidget(); ?>
  </div>
<?php
$this->pageJS = <<<HTML
cur.uiAfishaChk = new Checkbox('afisha', {label: 'участвует ли в разделе Афиша'});
HTML;
