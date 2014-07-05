<?php
/** @var MarketGood $good
 * @var ActiveForm $form
 */
?>
<div class="market_form_content">
  <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
    'id' => 'type_form'
  )); ?>
  <div id="market_good_result"></div>
  <div id="market_good_error" class="error"></div>
  <div class="market_form_header"><?php echo $good->getAttributeLabel('category_id') ?></div>
  <div class="market_form_param"><?php echo ActiveHtml::hiddenField('category_id', $good->category_id) ?></div>
  <div class="market_form_header"><?php echo $good->getAttributeLabel('name') ?></div>
  <div class="market_form_param"><?php echo ActiveHtml::textField('name', $good->name, array('class' => 'text')) ?></div>
  <div class="market_form_header"><?php echo $good->getAttributeLabel('facephoto') ?></div>
  <div class="market_form_param"><?php echo ActiveHtml::hiddenField('facephoto', $good->facephoto) ?></div>
  <div class="market_form_header"><?php echo $good->getAttributeLabel('shortstory') ?></div>
  <div class="market_form_param"><?php echo ActiveHtml::textArea('shortstory', $good->shortstory, array('class' => 'text')) ?></div>
  <div class="market_form_header"><?php echo $good->getAttributeLabel('price') ?></div>
  <div class="market_form_param">
    <?php echo ActiveHtml::textField('price', $good->price, array('class' => 'text')) ?>
  </div>
  <div class="market_form_header"><?php echo $good->getAttributeLabel('discount') ?></div>
  <div class="market_form_param"><?php echo ActiveHtml::textField('discount', $good->discount, array('class' => 'text')) ?></div>
  <?php $this->endWidget(); ?>
</div>