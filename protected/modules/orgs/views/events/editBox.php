<?php
/**
 * @var Event $event
 * @var Profile $profile
 */

Yii::app()->getClientScript()->registerCssFile('/css/upload.css');
Yii::app()->getClientScript()->registerScriptFile('/js/upload.js');

$eventTypesJS = array();
$roomsJS = array();

foreach ($eventTypes as $eventType) {
  $eventTypesJS[] = "[". $eventType->type_id .",'". $eventType->type_name ."']";
}
if ($event->org->rooms) {
  foreach ($event->org->rooms as $room) {
    $roomsJS[] = "[". $room->room_id .",'". $room->name ."']";
  }
}

$eventTypesJS = implode(",", $eventTypesJS);
$roomsJS = implode(",", $roomsJS);

$this->pageTitle = 'Редактировать событие';
?>
  <div class="event_content">
    <div id="event_result"></div>
    <div id="event_error" class="error"></div>
    <div class="event_general_row clear_fix">
      <div class="event_general_label fl_l ta_r"><?php echo $event->getAttributeLabel('event_type_id') ?>:</div>
      <div class="event_general_labeled fl_l"><?php echo ActiveHtml::hiddenField('event_type_id', $event->event_type_id) ?></div>
    </div>
    <?php if ($event->org->rooms): ?>
      <div class="event_general_row clear_fix">
        <div class="event_general_label fl_l ta_r"><?php echo $event->getAttributeLabel('room_id') ?>:</div>
        <div class="event_general_labeled fl_l"><?php echo ActiveHtml::hiddenField('room_id', $event->room_id) ?></div>
      </div>
    <?php endif; ?>
    <div class="event_general_row clear_fix">
      <div class="event_general_label fl_l ta_r"><?php echo $event->getAttributeLabel('photo') ?>:</div>
      <div class="event_general_labeled fl_l">
        <div class="upload_wrap"><?php echo ActiveHtml::hiddenField('photo', $event->photo) ?></div>
      </div>
    </div>
    <div class="event_general_row clear_fix">
      <div class="event_general_label fl_l ta_r"><?php echo $event->getAttributeLabel('title') ?>:</div>
      <div class="event_general_labeled fl_l"><?php echo ActiveHtml::textField('title', $event->title, array('class' => 'text')) ?></div>
    </div>
    <div class="event_general_row clear_fix">
      <div class="event_general_label fl_l ta_r"><?php echo $event->getAttributeLabel('shortstory') ?>:</div>
      <div class="event_general_labeled fl_l"><?php echo ActiveHtml::textArea('shortstory', $event->shortstory, array('class' => 'text', 'onkeyup' => 'checkTextLength(this, 4096, \'#shortstory_warn\')')) ?></div>
    </div>
    <div id="event_start_time" class="event_general_row clear_fix"<?php if (!$event->start_time): ?> style="display: none"<?php endif; ?>>
      <div class="event_general_label fl_l ta_r"><?php echo $event->getAttributeLabel('start_time') ?>:</div>
      <div class="event_general_labeled fl_l">
        <div class="fl_l">
          <?php
          $source_tz = new DateTimeZone("Europe/Moscow");
          $target_tz = new DateTimeZone($event->org->city->timezone);

          if ($event->start_time) {
            $start = new DateTime($event->start_time, $source_tz);
            $start->setTimezone($target_tz);
          }
          if ($event->end_time) {
            $end = new DateTime($event->end_time, $source_tz);
            $end->setTimezone($target_tz);
          }

          ?>
          <?php echo ActiveHtml::hiddenField('ch_st', ($event->start_time) ? 1 : 0) ?>
          <?php echo ActiveHtml::hiddenField('start_time', ($event->start_time) ? $start->format("Y-m-d H:i") : date("Y-m-d") ." 00:00") ?>
        </div>
        <div class="fl_l edit_at" style="padding: 4px 8px 0px">в</div>
        <div class="fl_l">
          <div class="fl_l">
            <?php echo ActiveHtml::hiddenField('hours', ($event->start_time) ? $start->format('G') : '') ?>
          </div>
          <div class="fl_l" style="padding: 5px 3px 0px"> : </div>
          <div class="fl_l">
            <?php echo ActiveHtml::hiddenField('minutes', ($event->start_time) ? $start->format('i') : '') ?>
          </div>
        </div>
      </div>
    </div>
    <?php if (!$event->start_time): ?>
    <div id="add_event_start_label">
      <div class="event_general_label fl_l ta_r">&nbsp;</div>
      <div class="event_general_labeled fl_l">
        <a onclick="$('#add_event_start_label').hide(); $('#event_start_time').show(); $('#ch_st').val(1)">Указать время начала</a>
      </div>
      <br class="clear">
    </div>
    <?php endif; ?>
    <div id="event_end_time" class="event_general_row clear_fix"<?php if (!$event->end_time): ?> style="display: none"<?php endif; ?>>
      <div class="event_general_label fl_l ta_r"><?php echo $event->getAttributeLabel('end_time') ?>:</div>
      <div class="event_general_labeled fl_l">
        <div class="fl_l">
          <?php echo ActiveHtml::hiddenField('ch_et', ($event->end_time) ? 1 : 0) ?>
          <?php echo ActiveHtml::hiddenField('end_time', ($event->end_time) ? $end->format("Y-m-d H:i") : date("Y-m-d", (time() + 86400)) ." 00:00") ?>
        </div>
        <div class="fl_l edit_at" style="padding: 4px 8px 0px">в</div>
        <div class="fl_l">
          <div class="fl_l">
            <?php echo ActiveHtml::hiddenField('hours_end', ($event->end_time) ? $end->format('G') : '') ?>
          </div>
          <div class="fl_l" style="padding: 5px 3px 0px"> : </div>
          <div class="fl_l">
            <?php echo ActiveHtml::hiddenField('minutes_end', ($event->end_time) ? $end->format('i') : '') ?>
          </div>
        </div>
      </div>
    </div>
    <?php if (!$event->end_time): ?>
    <div id="add_event_finish_label">
      <div class="event_general_label fl_l ta_r">&nbsp;</div>
      <div class="event_general_labeled fl_l">
        <a onclick="$('#add_event_finish_label').hide(); $('#event_end_time').show(); $('#ch_et').val(1)">Указать время окончания</a>
      </div>
      <br class="clear">
    </div>
    <?php endif; ?>
    <div class="event_general_row clear_fix">
      <div class="event_general_label fl_l"></div>
      <div class="event_general_labeled fl_l"><?php echo ActiveHtml::hiddenField('weekly', ($event->weekly == "") ? 0 : 1) ?></div>
    </div>
    <div id="event_weekly_bar" class="clear_fix"<?php if ($event->weekly == ''): ?> style="display: none"<?php endif; ?>>
      <div class="event_general_label fl_l"></div>
      <div class="event_general_labeled fl_l">
      <?php $weekly = explode(",", $event->weekly); ?>
        <a class="fl_l event_weekly_dow<?php if (in_array('Mon', $weekly)) echo " selected" ?>" onclick="$(this).toggleClass('selected')">Пн</a>
        <a class="fl_l event_weekly_dow<?php if (in_array('Tue', $weekly)) echo " selected" ?>" onclick="$(this).toggleClass('selected')">Вт</a>
        <a class="fl_l event_weekly_dow<?php if (in_array('Wed', $weekly)) echo " selected" ?>" onclick="$(this).toggleClass('selected')">Ср</a>
        <a class="fl_l event_weekly_dow<?php if (in_array('Thu', $weekly)) echo " selected" ?>" onclick="$(this).toggleClass('selected')">Чт</a>
        <a class="fl_l event_weekly_dow<?php if (in_array('Fri', $weekly)) echo " selected" ?>" onclick="$(this).toggleClass('selected')">Пт</a>
        <a class="fl_l event_weekly_dow<?php if (in_array('Sat', $weekly)) echo " selected" ?>" onclick="$(this).toggleClass('selected')">Сб</a>
        <a class="fl_l event_weekly_dow<?php if (in_array('Sun', $weekly)) echo " selected" ?>" onclick="$(this).toggleClass('selected')">Вс</a>
      </div>
    </div>
    <div class="event_general_row clear_fix">
      <div class="event_general_label fl_l ta_r"><?php echo $event->getAttributeLabel('price') ?>:</div>
      <div class="event_general_labeled fl_l"><?php echo ActiveHtml::textField('price', $event->price, array('class' => 'text')) ?></div>
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
