<?php
$this->pageTitle=Yii::app()->name . ' - Пользователи';

Yii::app()->getClientScript()->registerCssFile('/css/users.css');
Yii::app()->getClientScript()->registerScriptFile('/js/users.js');

$citiesList = City::getDataArray();
$citiesJS = array();

foreach ($roles as $role) {
  $rolesJs[] = "'". $role->name ."'";
}
foreach ($citiesList as $city_name => $city_id) {
  $citiesJS[] = "[". $city_id .",'". $city_name ."']";
}
$citiesJS = implode(",", $citiesJS);
$rolesSelectJS = implode(",", $rolesJs);

$delta = Yii::app()->controller->module->usersPerPage;

$url_suffix = array();
if (sizeof($c) > 0) {
  foreach ($c as $c_key => $c_value) {
    $url_suffix[] = "c[". $c_key ."]=". urlencode($c_value);
  }
}
?>

<div class="users_search_wrap clear_fix">
  <div class="fl_l">
    <?php echo ActiveHtml::textField('c[name]', (isset($c['name'])) ? $c['name'] : '', array('class' => 'text', 'placeholder' => 'Начните вводить имя или номер телефона')) ?>
  </div>
  <div class="fl_l" style="margin-left: 10px">
    <input type="hidden" id="c[city_id]" name="c[city_id]" value="<?php echo (isset($c['city_id'])) ? $c['city_id'] : '' ?>">
  </div>
  <div class="fl_l" style="margin-left: 10px">
    <input type="hidden" id="c[role]" name="c[role]" value="<?php echo (isset($c['role'])) ? $c['role'] : '' ?>">
  </div>
  <div class="fl_r">
    <div class="button_blue">
      <button onclick="Users.addUser()">Добавить пользователя</button>
    </div>
  </div>
</div>
<div class="summary_wrap">
  <?php $this->renderPartial('//layouts/pages', array(
    'url' => '/users'. ((sizeof($url_suffix) > 0) ? '?'. implode('&', $url_suffix) : ''),
    'offset' => $offset,
    'offsets' => $offsets,
    'delta' => $delta,
  )) ?>
  <div class="fl_r progress"></div>
  <div class="summary"><?php echo Yii::t('app', '{n} пользователь|{n} пользователя|{n} пользователей', $offsets) ?></div>
</div>

<div class="results_wrap">
  <table class="data_table">
    <thead>
      <tr>
        <th>#</th>
        <th>E-Mail</th>
        <th>Имя пользователя</th>
        <th>Город</th>
        <th>Роль</th>
        <th></th>
      </tr>
    </thead>
    <tbody rel="pagination">
    <?php echo $this->renderPartial('_userlist', array('users' => $users, 'offset' => $offset)) ?>
    </tbody>
  </table>
</div>

<script type="text/javascript">
var rolesList = [<?php echo (isset($rolesJs)) ? implode(',', $rolesJs) : '' ?>];
</script>

<?php
$this->pageJS = <<<HTML
Users.init({cities: [$citiesJS], roles: [$rolesSelectJS]});
HTML;

