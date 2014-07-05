<div class="advert_param_content">
  <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
    'id' => 'advert_param_form'
  )); ?>
  <div id="advert_param_result"></div>
  <div id="advert_param_error" class="error"></div>
  <div class="advert_param_general_row clear_fix">
    <div class="advert_param_general_label fl_l ta_r"><?php echo $param->getAttributeLabel('category_id') ?>:</div>
    <div class="advert_param_general_labeled fl_l"><?php echo ActiveHtml::hiddenField('category_id', ($param->category_id) ? $param->category_id : '') ?></div>
  </div>
  <div class="advert_param_general_row clear_fix">
    <div class="advert_param_general_label fl_l ta_r"><?php echo $param->getAttributeLabel('parent_id') ?>:</div>
    <div class="advert_param_general_labeled fl_l"><?php echo ActiveHtml::hiddenField('parent_id', ($param->parent_id) ? $param->parent_id : '') ?></div>
  </div>
  <div class="advert_param_general_row clear_fix">
    <div class="advert_param_general_label fl_l ta_r"><?php echo $param->getAttributeLabel('title') ?>:</div>
    <div class="advert_param_general_labeled fl_l"><?php echo ActiveHtml::textField('title', ($param->title) ? $param->title : '', array('class' => 'text', 'onkeypress' => 'onCtrlEnter(event, AdvertParam.attemptAdd)')) ?></div>
  </div>
  <div class="advert_param_general_row clear_fix">
    <div class="advert_param_general_label fl_l ta_r"><?php echo $param->getAttributeLabel('type') ?>:</div>
    <div class="advert_param_general_labeled fl_l"><?php echo ActiveHtml::hiddenField('type', ($param->type) ? $param->type : '') ?></div>
  </div>
  <div id="suffix_wrap" class="advert_param_general_row clear_fix"<?php if (!$param->suffix): ?> style="display: none"<?php endif; ?>>
    <div class="advert_param_general_label fl_l ta_r"><?php echo $param->getAttributeLabel('suffix') ?>:</div>
    <div class="advert_param_general_labeled fl_l"><?php echo ActiveHtml::textField('suffix', ($param->suffix) ? $param->suffix : '', array('class' => 'text')) ?></div>
  </div>
  <?php $this->endWidget(); ?>
</div>