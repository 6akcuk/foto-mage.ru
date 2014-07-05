<?php
$this->pageTitle = Yii::app()->name . ' - Список городов';

Yii::app()->getClientScript()->registerCssFile('/css/cities.css');
Yii::app()->getClientScript()->registerScriptFile('/js/cities.js');

$delta = Yii::app()->getModule('unify')->citiesPerPage;
?>
<div class="city_search_wrap clear_fix">
  <div rel="filters" class="fl_l">
    <?php echo ActiveHtml::textField('c[name]', (isset($c['name'])) ? $c['name'] : '', array('class' => 'text', 'placeholder' => 'Начните вводить название города')) ?>
  </div>
  <div class="fl_r">
    <div class="button_blue">
      <button onclick="City.add()">Добавить город</button>
    </div>
  </div>
</div>
<div class="summary_wrap">
  <?php $this->renderPartial('//layouts/pages', array(
    'url' => '/system/cities/index',
    'offset' => $offset,
    'offsets' => $offsets,
    'delta' => $delta,
  )) ?>
  <div class="summary"><?php echo Yii::t('app', '{n} город|{n} города|{n} городов', $offsets) ?></div>
</div>
<div class="city_wrap">
<?php if ($cities): ?>
  <table class="users_table">
    <thead>
    <tr>
      <th>#</th>
      <th>Название города</th>
      <th>Часовой пояс</th>
      <th></th>
    </tr>
    </thead>
    <tbody rel="pagination">
    <?php echo $this->renderPartial('_citylist', array('cities' => $cities, 'offset' => $offset)) ?>
    </tbody>
  </table>
<?php else: ?>
  <div id="no_results">Города еще не добавлены</div>
<?php endif; ?>
</div>
<?php
$this->pageJS = <<<HTML
City.init();
HTML;
