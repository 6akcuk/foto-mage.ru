<?php
/** @var DiscountAction $action
 * @var ActiveForm $form
 */
?>
<div class="org_form_content">
  <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
    'id' => 'discount_action_form'
  )); ?>
  <div id="org_result"></div>
  <div id="org_error" class="error"></div>
  <div class="event_general_row clear_fix">
    <div class="event_general_label fl_l ta_r">Название:</div>
    <div class="event_general_labeled fl_l"><?php echo ActiveHtml::textField('name', '', array('class' => 'text')) ?></div>
  </div>
  <div class="event_general_row clear_fix">
    <div class="event_general_label fl_l ta_r">Изображение:</div>
    <div class="event_general_labeled fl_l">
      <div class="upload_wrap"><?php echo ActiveHtml::hiddenField('banner', '') ?></div>
    </div>
  </div>
  <div class="event_general_row clear_fix">
    <div class="event_general_label fl_l ta_r">Владелец:</div>
    <div class="event_general_labeled fl_l"><?php echo ActiveHtml::textField('owner_id', '', array('class' => 'text')) ?></div>
  </div>
  <?php $this->endWidget(); ?>
</div>