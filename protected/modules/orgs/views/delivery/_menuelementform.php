<?php
/** @var DeliveryMenuElement $element
 * @var ActiveForm $form
 */
?>
<div class="delivery_form_content">
  <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
    'id' => 'type_form'
  )); ?>
  <div id="delivery_result"></div>
  <div id="delivery_error" class="error"></div>
  <div class="delivery_form_header"><?php echo $element->getAttributeLabel('category_id') ?></div>
  <div class="delivery_form_param"><?php echo ActiveHtml::hiddenField('category_id', $element->category_id) ?></div>
  <div class="delivery_form_header"><?php echo $element->getAttributeLabel('icon') ?></div>
  <div class="delivery_form_param"><?php echo ActiveHtml::hiddenField('icon', $element->icon) ?></div>
  <div class="delivery_form_header"><?php echo $element->getAttributeLabel('name') ?></div>
  <div class="delivery_form_param"><?php echo ActiveHtml::textField('name', $element->name, array('class' => 'text')) ?></div>
  <?php $this->endWidget(); ?>
</div>