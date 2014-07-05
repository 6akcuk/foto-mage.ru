<?php
/** @var DeliveryCategory $category
 * @var ActiveForm $form
 */
?>
<div class="delivery_form_content">
  <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
    'id' => 'type_form'
  )); ?>
  <div id="delivery_result"></div>
  <div id="delivery_error" class="error"></div>
  <div class="delivery_form_header"><?php echo $category->getAttributeLabel('name') ?></div>
  <?php echo ActiveHtml::textField('name', $category->name, array('class' => 'text')) ?>
  <?php $this->endWidget(); ?>
</div>