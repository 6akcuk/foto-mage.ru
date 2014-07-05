<?php
$this->pageTitle = Yii::app()->name . ' - Список организаций';

Yii::app()->getClientScript()->registerCssFile('/css/orgs.css');
Yii::app()->getClientScript()->registerScriptFile('/js/orgs.js');

$citiesList = City::getDataArray();
$citiesJS = array();
$typesJs = array();

foreach ($types as $type) {
  $typesJs[] = "[". $type->type_id .",'". $type->type_name ."']";
}
foreach ($citiesList as $city_name => $city_id) {
  $citiesJS[] = "[". $city_id .",'". $city_name ."']";
}
$citiesJS = implode(",", $citiesJS);
$typesSelectJS = implode(",", $typesJs);

$delta = Yii::app()->getModule('orgs')->orgsPerPage;

$url_suffix = array();
if (sizeof($c) > 0) {
  foreach ($c as $c_key => $c_value) {
    $url_suffix[] = "c[". $c_key ."]=". urlencode($c_value);
  }
}
?>

<div class="data_table_filters clear_fix">
  <div class="data_table_filter_block" style="width: 245px">
    <div class="data_table_filter_label">Поиск по названию</div>
    <div class="clear_fix">
      <?php echo ActiveHtml::textField('c[name]', (isset($c['name'])) ? $c['name'] : '', array('class' => 'text', 'placeholder' => 'Поиск')) ?>
    </div>
  </div>
  <div class="data_table_filter_block" style="width: 245px">
    <div class="data_table_filter_label">Город</div>
    <div class="clear_fix">
      <input type="hidden" id="c[city_id]" name="c[city_id]" value="<?php echo (isset($c['city_id'])) ? $c['city_id'] : '' ?>">
    </div>
  </div>
  <div class="data_table_filter_block" style="width: 245px">
    <div class="data_table_filter_label">Категория</div>
    <div class="clear_fix">
      <input type="hidden" id="c[org_type_id]" name="c[org_type_id]" value="<?php echo (isset($c['org_type_id'])) ? $c['org_type_id'] : '' ?>">
    </div>
  </div>
  <div class="fl_r" style="padding: 15px 15px 0px 0px">
    <div class="button_blue">
      <button onclick="Org.add()">Добавить организацию</button>
    </div>
  </div>
</div>

<div class="summary_wrap clear_fix">
  <?php $this->renderPartial('//layouts/pages', array(
    'url' => '/orgs/orgs/index'. ((sizeof($url_suffix) > 0) ? '?'. implode('&', $url_suffix) : ''),
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
      <th colspan="2">Организация <span id="data_table_count"><?php echo $offsets ?></span></th>
      <th>Город</th>
      <th>Адрес</th>
      <th>Действия</th>
    </tr>
    </thead>
    <tbody rel="pagination">
    <?php echo $this->renderPartial('_orglist', array('orgs' => $orgs, 'offset' => $offset)) ?>
    </tbody>
  </table>
</div>
<?php
$this->pageJS = <<<HTML
Org.init({cities: [$citiesJS], types: [$typesSelectJS]});
HTML;
