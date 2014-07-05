<?php
/**
 * @var Event $event
 */
$this->pageTitle = Yii::app()->name . ' - События '. $org->name;

Yii::app()->getClientScript()->registerCssFile('/css/page.css');
Yii::app()->getClientScript()->registerCssFile('/css/orgs.css');
Yii::app()->getClientScript()->registerScriptFile('/js/orgs.js');

$rooms = array();
foreach ($org->rooms as $room) {
  $rooms[$room->room_id] = $room;
}

$delta = Yii::app()->getModule('orgs')->orgsPerPage;
?>
<div class="orgs_search_wrap clear_fix">
  <div rel="filters" class="fl_l">
    <?php echo ActiveHtml::textField('c[name]', (isset($c['name'])) ? $c['name'] : '', array('class' => 'text', 'placeholder' => 'Начните вводить название события')) ?>
  </div>
  <div class="fl_r">
    <div class="button_blue">
      <button onclick="Org.addEvent(<?php echo $org->org_id ?>)">Добавить событие</button>
    </div>
  </div>
</div>
<div class="events_content_wrap">
<?php if (sizeof($events)): ?>
  <?php foreach ($events as $room_id => $events_data): ?>
  <div class="module">
    <div class="module_header">
      <div class="header_top"><?php echo ($room_id > 0) ? $rooms[$room_id]->name : 'Общее' ?></div>
      <div class="p_header_bottom"><?php echo Yii::t('app', '{n} событие|{n} события|{n} событий', $events_data['num']) ?></div>
    </div>
    <div class="module_body">
    <?php foreach ($events_data['list'] as $event): ?>
      <div id="event<?php echo $event->event_id ?>" class="event_row clear_fix">
        <div class="fl_l event_row_photo">
          <?php echo ActiveHtml::showUploadImage($event->photo, 'b') ?>
        </div>
        <div class="event_row_info fl_l">
          <div class="event_row_title"><?php echo $event->title ?></div>
          <div class="event_row_shortstory">
            <?php echo $event->shortstory ?>
          </div>
          <?php if ($event->start_time): ?>
          <div class="event_row_labeled">
            Начало: <?php echo ActiveHtml::date($event->start_time) ?>
          </div>
          <?php endif; ?>
          <?php if ($event->end_time): ?>
            <div class="event_row_labeled">
              Окончание: <?php echo ActiveHtml::date($event->end_time) ?>
            </div>
          <?php endif; ?>
          <?php if ($event->price): ?>
            <div class="event_row_labeled">
              <?php echo $event->price ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="event_row_actions fl_r">
          <div>
            <div class="button_blue">
              <button onclick="Org.editEvent(<?php echo $event->event_id ?>)">Редактировать</button>
            </div>
          </div>
          <div>
            <div class="button_blue">
              <button onclick="Org.deleteEvent(<?php echo $event->event_id ?>)">Удалить</button>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
<?php else: ?>
  <div id="events_empty">
    Здесь будут отображаться все события организации
  </div>
<?php endif; ?>
</div>
<?php
$this->pageJS = <<<HTML
Org.initEvents();
HTML;
