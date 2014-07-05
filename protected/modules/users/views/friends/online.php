<?php
/** @var $people User */

Yii::app()->getClientScript()->registerCssFile('/css/profile.css');
Yii::app()->getClientScript()->registerCssFile('/css/search.css');

Yii::app()->getClientScript()->registerScriptFile('/js/profile.js');

$this->pageTitle = Yii::app()->name .' - Друзья онлайн';
$delta = Yii::app()->controller->module->friendsPerPage;

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
<div class="tabs">
  <?php echo ActiveHtml::link('Все друзья', '/friends?id='. $user->id) ?>
  <?php echo ActiveHtml::link('Друзья онлайн', '/friends?id='. $user->id .'&section=online', array('class' => 'selected')) ?>
  <?php if ($user->id == Yii::app()->user->getId()): ?>
  <?php echo ActiveHtml::link('Заявки в друзья'. (($this->pageCounters['friends'] > 0) ? ' <b>+'. $this->pageCounters['friends'] .'</b>' : ''), '/friends?id='. $user->id .'&section=requests') ?>
  <?php endif; ?>
</div>
<div class="friends_search_wrap clear_fix">
  <div class="fl_l">
    <?php echo ActiveHtml::textField('c[name]', (isset($c['name'])) ? $c['name'] : '', array('class' => 'text', 'placeholder' => 'Поиск друга по имени')) ?>
  </div>
  <div class="fl_l">
    <div class="button_blue">
      <button onclick="return nav.go(this, event, null)">Поиск</button>
    </div>
  </div>
</div>
<div class="summary_wrap">
  <?php $this->renderPartial('//layouts/pages', array(
    'url' => '/friends?id='. $user->id .'&section=online&'. ((sizeof($url_suffix) > 0) ? ''. implode('&', $url_suffix) : ''),
    'offset' => $offset,
    'offsets' => $offsets,
    'delta' => $delta,
  )) ?>
  <div class="fl_r progress"></div>
  <div class="summary">У <?php if ($user->id == Yii::app()->user->getId()): ?>Вас<?php else: ?><?php echo ActiveHtml::lex(2, $user->getDisplayName()) ?><?php endif; ?> <?php echo Yii::t('user', '{n} друг|{n} друга|{n} друзей', $offsets) ?> онлайн</div>
</div>
<table class="gsearch_table">
  <tr>
    <td rel="pagination" class="searchresults">
      <?php if ($peoples): ?>
        <?php echo $this->renderPartial('_people', array('user' => $user, 'peoples' => $peoples, 'offset' => $offset)) ?>
      <?php else: ?>
        <div id="no_results">
          У вас нет друзей в сети.
        </div>
      <?php endif; ?>
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
Profile.initFriends({cities: [$citiesJS], roles: [$rolesJS]});
HTML;
