<?php
/** @var $people User */

Yii::app()->getClientScript()->registerCssFile('/css/profile.css');

Yii::app()->getClientScript()->registerCssFile('/css/search.css');
Yii::app()->getClientScript()->registerScriptFile('/js/search.js');

$this->pageTitle = Yii::app()->name .' - Люди';
$delta = Yii::app()->controller->module->peoplesPerPage;

$citiesList = City::getDataArray();
$citiesJS = array();

$roles = RbacItem::getSearchRoleArray();

foreach ($roles as $role) {
  $rolesJs[] = "'". $role ."'";
}
foreach ($citiesList as $city_name => $city_id) {
  $citiesJS[] = "[". $city_id .",'". $city_name ."']";
}
$citiesJS = implode(",", $citiesJS);
$rolesJS = implode(",", $rolesJs);

$url_suffix = array();
if (sizeof($c) > 0) {
  foreach ($c as $c_key => $c_value) {
    $url_suffix[] = "c[". $c_key ."]=". urlencode($c_value);
  }
}
?>
<div class="search_wrap clear_fix">
  <div rel="filters" class="fl_l">
    <?php echo ActiveHtml::textField('c[name]', (isset($c['name'])) ? $c['name'] : '', array('class' => 'text', 'placeholder' => 'Поиск по имени')) ?>
  </div>
  <div class="fl_l" style="margin-left: 10px">
    <div class="button_blue">
      <button onclick="return nav.go(this, event, null)">Поиск</button>
    </div>
  </div>
</div>
  <div class="summary_wrap">
    <?php $this->renderPartial('//layouts/pages', array(
      'url' => '/search?c[section]=people&'. ((sizeof($url_suffix) > 0) ? ''. implode('&', $url_suffix) : ''),
      'offset' => $offset,
      'offsets' => $offsets,
      'delta' => $delta,
    )) ?>
    <div class="fl_r progress"></div>
    <div class="summary"><?php echo Yii::t('user', 'Найден {n} пользователь|Найдено {n} пользователя|Найдено {n} пользователей', $offsets) ?></div>
  </div>
<table class="gsearch_table">
<tr>
  <td class="searchresults">
    <div rel="pagination">
      <?php if($peoples): ?>
        <?php echo $this->renderPartial('_people', array('peoples' => $peoples, 'offset' => $offset)) ?>
      <?php else: ?>
        <div id="no_results">
          Пользователи не найдены.
        </div>
      <?php endif; ?>
    </div>
  </td>
  <td class="filters">
    <div class="text">Фильтры поиска</div>
    <div>
      <input type="hidden" name="c[city_id]" id="c[city_id]" value="<?php echo ((isset($c['city_id'])) ? $c['city_id'] : '') ?>" />
    </div>
    <div style="margin-top: 10px">
      <input type="hidden" name="c[role]" id="c[role]" value="<?php echo ((isset($c['role'])) ? $c['role'] : '') ?>" />
    </div>
  </td>
</tr>
</table>

<?php
$this->pageJS = <<<HTML
Search.init({cities: [$citiesJS], roles: [$rolesJS]});
HTML;
