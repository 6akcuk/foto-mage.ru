<?php
$this->pageTitle = Yii::app()->name . ' - Список событий';

Yii::app()->getClientScript()->registerCssFile('/css/events.css');
Yii::app()->getClientScript()->registerScriptFile('/js/events.js');

$delta = Yii::app()->getModule('events')->eventsPerPage;
?>
  <div class="events_search_wrap clear_fix">
    <div rel="filters" class="fl_l">
      <?php echo ActiveHtml::textField('c[name]', (isset($c['name'])) ? $c['name'] : '', array('class' => 'text', 'placeholder' => 'Начните вводить название события')) ?>
    </div>
    <div class="fl_r">
      <div class="button_blue">
        <button onclick="Event.add()">Добавить событие</button>
      </div>
    </div>
  </div>
  <div class="summary_wrap clear_fix">
    <?php $this->renderPartial('//layouts/pages', array(
      'offset' => $offset,
      'offsets' => $offsets,
      'delta' => $delta,
    )) ?>
    <div class="summary"><?php echo Yii::t('app', '{n} событие|{n} события|{n} событий', $offsets) ?></div>
  </div>

  <div class="results_wrap">
    <table class="events_table">
      <thead>
      <tr>
        <th>Тип</th>
        <th width="">Имя</th>
        <th>Организация</th>
        <th>Действия</th>
      </tr>
      </thead>
      <tbody rel="pagination">
      <?php echo $this->renderPartial('_eventlist', array('events' => $events, 'offset' => $offset)) ?>
      </tbody>
    </table>
  </div>
<?php
$this->pageJS = <<<HTML
Event.init();
HTML;
