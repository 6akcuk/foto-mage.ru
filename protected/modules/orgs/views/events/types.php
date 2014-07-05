<?php
$this->pageTitle = Yii::app()->name . ' - Типы событий';

Yii::app()->getClientScript()->registerCssFile('/css/orgs.css');
Yii::app()->getClientScript()->registerScriptFile('/js/org_eventtypes.js');

$delta = Yii::app()->getModule('orgs')->eventTypesPerPage;
?>
<div class="orgs_search_wrap clear_fix">
  <div rel="filters" class="fl_l">
    <?php echo ActiveHtml::textField('c[type_name]', (isset($c['type_name'])) ? $c['type_name'] : '', array('class' => 'text', 'placeholder' => 'Начните вводить тип события')) ?>
  </div>
  <div class="fl_r">
    <div class="button_blue">
      <button onclick="OrgEventType.add()">Добавить новый тип</button>
    </div>
  </div>
</div>
<div class="summary_wrap clear_fix">
  <?php $this->renderPartial('//layouts/pages', array(
    'url' => '/orgs/events/types',
    'offset' => $offset,
    'offsets' => $offsets,
    'delta' => $delta,
  )) ?>
  <div class="summary"><?php echo Yii::t('app', '{n} тип|{n} типа|{n} типов', $offsets) ?></div>
</div>

<div class="results_wrap">
  <table class="orgs_table">
    <thead>
    <tr>
      <th width="">Имя</th>
      <th>Действия</th>
    </tr>
    </thead>
    <tbody rel="pagination">
    <?php echo $this->renderPartial('_eventtypelist', array('eventTypes' => $eventTypes, 'offset' => $offset)) ?>
    </tbody>
  </table>
</div>
<?php
$this->pageJS = <<<HTML
OrgEventType.init();
HTML;
