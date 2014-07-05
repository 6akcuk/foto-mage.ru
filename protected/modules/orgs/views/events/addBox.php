<?php
/**
 * @var User $event
 * @var Profile $profile
 */

Yii::app()->getClientScript()->registerCssFile('/css/upload.css');
Yii::app()->getClientScript()->registerScriptFile('/js/upload.js');

Yii::app()->getClientScript()->registerScriptFile('/js/orgs.js');

$eventTypesJS = array();
$roomsJS = array();

foreach ($eventTypes as $eventType) {
  $eventTypesJS[] = "[". $eventType->type_id .",'". $eventType->type_name ."']";
}
if ($org->rooms) {
  foreach ($org->rooms as $room) {
    $roomsJS[] = "[". $room->room_id .",'". $room->name ."']";
  }
}

$eventTypesJS = implode(",", $eventTypesJS);
$roomsJS = implode(",", $roomsJS);

$this->pageTitle = 'Добавить новое событие';
?>
<div class="event_content">
  <div id="event_result"></div>
  <div id="event_error" class="error"></div>
  <div class="event_general_row clear_fix">
    <div class="event_general_label fl_l ta_r"><?php echo $event->getAttributeLabel('event_type_id') ?>:</div>
    <div class="event_general_labeled fl_l"><?php echo ActiveHtml::hiddenField('event_type_id', '') ?></div>
  </div>
  <?php if ($org->rooms): ?>
  <div class="event_general_row clear_fix">
    <div class="event_general_label fl_l ta_r"><?php echo $event->getAttributeLabel('room_id') ?>:</div>
    <div class="event_general_labeled fl_l"><?php echo ActiveHtml::hiddenField('room_id', '') ?></div>
  </div>
  <?php endif; ?>
  <div class="event_general_row clear_fix">
    <div class="event_general_label fl_l ta_r"><?php echo $event->getAttributeLabel('photo') ?>:</div>
    <div class="event_general_labeled fl_l">
      <div class="upload_wrap"><?php echo ActiveHtml::hiddenField('photo', '') ?></div>
    </div>
  </div>
  <div class="event_general_row clear_fix">
    <div class="event_general_label fl_l ta_r"><?php echo $event->getAttributeLabel('title') ?>:</div>
    <div class="event_general_labeled fl_l"><?php echo ActiveHtml::textField('title', '', array('class' => 'text')) ?></div>
  </div>
  <div class="event_general_row clear_fix">
    <div class="event_general_label fl_l ta_r"><?php echo $event->getAttributeLabel('shortstory') ?>:</div>
    <div class="event_general_labeled fl_l"><?php echo ActiveHtml::textArea('shortstory', '', array('class' => 'text', 'onkeyup' => 'checkTextLength(this, 4096, \'#shortstory_warn\')')) ?></div>
  </div>
  <div id="event_start_time" class="event_general_row clear_fix" style="display: none">
    <div class="event_general_label fl_l ta_r"><?php echo $event->getAttributeLabel('start_time') ?>:</div>
    <div class="event_general_labeled fl_l">
      <div class="fl_l">
        <?php echo ActiveHtml::hiddenField('ch_st', 0) ?>
        <?php echo ActiveHtml::hiddenField('start_time', date("Y-m-d") ." 00:00") ?>
      </div>
      <div class="fl_l edit_at" style="padding: 4px 8px 0px">в</div>
      <div class="fl_l">
        <div class="fl_l">
          <?php echo ActiveHtml::hiddenField('hours', '') ?>
        </div>
        <div class="fl_l" style="padding: 5px 3px 0px"> : </div>
        <div class="fl_l">
          <?php echo ActiveHtml::hiddenField('minutes', '') ?>
        </div>
      </div>
    </div>
  </div>
  <div id="add_event_start_label">
    <div class="event_general_label fl_l ta_r">&nbsp;</div>
    <div class="event_general_labeled fl_l">
      <a onclick="$('#add_event_start_label').hide(); $('#event_start_time').show(); $('#ch_st').val(1)">Указать время начала</a>
    </div>
    <br class="clear">
  </div>
  <div id="event_end_time" class="event_general_row clear_fix" style="display: none">
    <div class="event_general_label fl_l ta_r"><?php echo $event->getAttributeLabel('end_time') ?>:</div>
    <div class="event_general_labeled fl_l">
      <div class="fl_l">
        <?php echo ActiveHtml::hiddenField('ch_et', 0) ?>
        <?php echo ActiveHtml::hiddenField('end_time', date("Y-m-d", (time() + 86400)) ." 00:00") ?>
      </div>
      <div class="fl_l edit_at" style="padding: 4px 8px 0px">в</div>
      <div class="fl_l">
        <div class="fl_l">
          <?php echo ActiveHtml::hiddenField('hours_end', '') ?>
        </div>
        <div class="fl_l" style="padding: 5px 3px 0px"> : </div>
        <div class="fl_l">
          <?php echo ActiveHtml::hiddenField('minutes_end', '') ?>
        </div>
      </div>
    </div>
  </div>
  <div id="add_event_finish_label">
    <div class="event_general_label fl_l ta_r">&nbsp;</div>
    <div class="event_general_labeled fl_l">
      <a onclick="$('#add_event_finish_label').hide(); $('#event_end_time').show(); $('#ch_et').val(1)">Указать время окончания</a>
    </div>
    <br class="clear">
  </div>
  <div class="event_general_row clear_fix">
    <div class="event_general_label fl_l"></div>
    <div class="event_general_labeled fl_l"><?php echo ActiveHtml::hiddenField('weekly') ?></div>
  </div>
  <div id="event_weekly_bar" class="clear_fix" style="display: none">
    <div class="event_general_label fl_l"></div>
    <div class="event_general_labeled fl_l">
      <a class="fl_l event_weekly_dow" onclick="$(this).toggleClass('selected')">Пн</a>
      <a class="fl_l event_weekly_dow" onclick="$(this).toggleClass('selected')">Вт</a>
      <a class="fl_l event_weekly_dow" onclick="$(this).toggleClass('selected')">Ср</a>
      <a class="fl_l event_weekly_dow" onclick="$(this).toggleClass('selected')">Чт</a>
      <a class="fl_l event_weekly_dow" onclick="$(this).toggleClass('selected')">Пт</a>
      <a class="fl_l event_weekly_dow" onclick="$(this).toggleClass('selected')">Сб</a>
      <a class="fl_l event_weekly_dow" onclick="$(this).toggleClass('selected')">Вс</a>
    </div>
  </div>
  <div class="event_general_row clear_fix">
    <div class="event_general_label fl_l ta_r"><?php echo $event->getAttributeLabel('price') ?>:</div>
    <div class="event_general_labeled fl_l"><?php echo ActiveHtml::textField('price', '', array('class' => 'text')) ?></div>
  </div>
</div>
<?php
$this->pageJS = <<<HTML
Org.initAddEvent({
  uploadAction: 'http://cs1.e-bash.me/upload.php',
  eventTypes: [$eventTypesJS],
  rooms: [$roomsJS]
});
HTML;
