<?php
$this->pageTitle = Yii::app()->name . ' - Настройка параметров';

Yii::app()->getClientScript()->registerCssFile('/css/orgs.css');
Yii::app()->getClientScript()->registerScriptFile('/js/org_types.js');

$delta = Yii::app()->getModule('orgs')->orgTypesPerPage;
?>

<div class="data_table_filters clear_fix">
  <div class="data_table_filter_block" style="width: 245px">
    <div class="data_table_filter_label">Поиск по названию</div>
    <div class="clear_fix">
      <?php echo ActiveHtml::textField('c[type_name]', (isset($c['type_name'])) ? $c['type_name'] : '', array('class' => 'text', 'placeholder' => 'Поиск')) ?>
    </div>
  </div>
  <div class="fl_r" style="padding: 15px 15px 0px 0px">
    <div class="button_blue">
      <button onclick="OrgType.add()">Добавить новый тип</button>
    </div>
  </div>
</div>

<div class="summary_wrap clear_fix">
  <?php $this->renderPartial('//layouts/pages', array(
    'url' => '/orgs/default/index',
    'offset' => $offset,
    'offsets' => $offsets,
    'delta' => $delta,
  )) ?>
  <div class="fl_r progress"></div>
</div>

<div class="results_wrap">
  <table class="data_table">
    <thead>
    <tr>
      <th>Категория <span id="data_table_count"><?php echo $offsets ?></span></th>
      <th>Действия</th>
    </tr>
    </thead>
    <tbody rel="pagination">
    <?php echo $this->renderPartial('_typelist', array('types' => $types, 'offset' => $offset)) ?>
    </tbody>
  </table>
</div>
<?php
$this->pageJS = <<<HTML
OrgType.init();
HTML;
