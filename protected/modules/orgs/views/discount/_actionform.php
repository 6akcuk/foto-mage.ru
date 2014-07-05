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
    <div class="event_general_label fl_l ta_r"><?php echo $action->getAttributeLabel('name') ?>:</div>
    <div class="event_general_labeled fl_l"><?php echo ActiveHtml::textField('name', $action->name, array('class' => 'text')) ?></div>
  </div>
  <div class="event_general_row clear_fix">
    <div class="event_general_label fl_l ta_r"><?php echo $action->getAttributeLabel('fullstory') ?>:</div>
    <div class="event_general_labeled fl_l"><?php echo ActiveHtml::textArea('fullstory', $action->fullstory, array('class' => 'text')) ?></div>
  </div>
  <div class="event_general_row clear_fix">
    <div class="event_general_label fl_l ta_r"><?php echo $action->getAttributeLabel('banner') ?>:</div>
    <div class="event_general_labeled fl_l">
      <div class="upload_wrap"><?php echo ActiveHtml::hiddenField('banner', $action->banner) ?></div>
    </div>
  </div>
  <div class="event_general_row clear_fix">
    <div class="event_general_label fl_l ta_r"><?php echo $action->getAttributeLabel('pc_limits') ?>:</div>
    <div class="event_general_labeled fl_l"><?php echo ActiveHtml::textField('pc_limits', $action->pc_limits, array('class' => 'text')) ?></div>
  </div>
  <div id="action_start_time" class="event_general_row clear_fix"<?php if (!$action->start_time): ?> style="display: none"<?php endif; ?>>
    <div class="event_general_label fl_l ta_r"><?php echo $action->getAttributeLabel('start_time') ?>:</div>
    <div class="event_general_labeled fl_l">
      <div class="clear_fix">
        <?php echo ActiveHtml::hiddenField('ch_st', ($action->start_time) ? 1 : 0) ?>
        <div class="fl_l"><?php echo ActiveHtml::hiddenField('start_time', ($action->start_time) ? $action->start_time : date("Y-m-d") ." 00:00") ?></div>
        <a class="fl_l" style="padding: 3px 0px 0px 10px" onclick="$('#add_action_start_label').show(); $('#action_start_time').hide(); $('#ch_st').val(0)">Удалить</a>
      </div>
    </div>
  </div>
  <div id="add_action_start_label"<?php if ($action->start_time): ?> style="display: none"<?php endif; ?>>
    <div class="event_general_label fl_l ta_r">&nbsp;</div>
    <div class="event_general_labeled fl_l">
      <a onclick="$('#add_action_start_label').hide(); $('#action_start_time').show(); $('#ch_st').val(1)">Указать время начала</a>
    </div>
    <br class="clear">
  </div>
  <div id="action_end_time" class="event_general_row clear_fix"<?php if (!$action->end_time): ?> style="display: none"<?php endif; ?>>
    <div class="event_general_label fl_l ta_r"><?php echo $action->getAttributeLabel('end_time') ?>:</div>
    <div class="event_general_labeled fl_l">
      <div class="clear_fix">
        <?php echo ActiveHtml::hiddenField('ch_et', ($action->end_time) ? 1 : 0) ?>
        <div class="fl_l"><?php echo ActiveHtml::hiddenField('end_time', ($action->end_time) ? $action->end_time : date("Y-m-d", (time() + 86400)) ." 00:00") ?></div>
        <a class="fl_l" style="padding: 3px 0px 0px 10px" onclick="$('#add_action_finish_label').show(); $('#action_end_time').hide(); $('#ch_et').val(0)">Удалить</a>
      </div>
    </div>
  </div>
  <div id="add_action_finish_label"<?php if ($action->end_time): ?> style="display: none"<?php endif; ?>>
    <div class="event_general_label fl_l ta_r">&nbsp;</div>
    <div class="event_general_labeled fl_l">
      <a onclick="$('#add_action_finish_label').hide(); $('#action_end_time').show(); $('#ch_et').val(1)">Указать время окончания</a>
    </div>
    <br class="clear">
  </div>
  <?php $this->endWidget(); ?>
</div>