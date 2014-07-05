<?php
/** @var DeliveryGood $good
 * @var ActiveForm $form
 */
?>
<div class="delivery_form_content">
  <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
    'id' => 'type_form'
  )); ?>
  <div id="delivery_result"></div>
  <div id="delivery_error" class="error"></div>
  <div class="delivery_form_header"><?php echo $good->getAttributeLabel('element_id') ?></div>
  <div class="delivery_form_param"><?php echo ActiveHtml::hiddenField('element_id', $good->element_id) ?></div>
  <div class="delivery_form_header"><?php echo $good->getAttributeLabel('name') ?></div>
  <div class="delivery_form_param"><?php echo ActiveHtml::textField('name', $good->name, array('class' => 'text')) ?></div>
  <div class="delivery_form_header"><?php echo $good->getAttributeLabel('facephoto') ?></div>
  <div class="delivery_form_param"><?php echo ActiveHtml::hiddenField('facephoto', $good->facephoto) ?></div>
  <div class="delivery_form_header"><?php echo $good->getAttributeLabel('shortstory') ?></div>
  <div class="delivery_form_param"><?php echo ActiveHtml::textArea('shortstory', $good->shortstory, array('class' => 'text')) ?></div>
  <div class="delivery_form_header"><?php echo $good->getAttributeLabel('price') ?></div>
  <div class="delivery_form_param">
    <?php echo ActiveHtml::textField('price', $good->price, array('class' => 'text')) ?>
  </div>
  <div class="delivery_form_header"><?php echo $good->getAttributeLabel('discount') ?></div>
  <div class="delivery_form_param"><?php echo ActiveHtml::textField('discount', $good->discount, array('class' => 'text')) ?></div>
  <?php $this->endWidget(); ?>
</div>