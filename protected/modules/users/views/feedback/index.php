<?php
$this->pageTitle=Yii::app()->name . ' - Обратная связь';

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

$delta = Yii::app()->controller->module->feedbacksPerPage;

$url_suffix = array();
if (sizeof($c) > 0) {
  foreach ($c as $c_key => $c_value) {
    $url_suffix[] = "c[". $c_key ."]=". urlencode($c_value);
  }
}
?>
  <div class="users_search_wrap clear_fix">
    <div class="fl_l">
      <?php echo ActiveHtml::textField('c[name]', (isset($c['name'])) ? $c['name'] : '', array('class' => 'text', 'placeholder' => 'Начните вводить текст поиска')) ?>
    </div>
    <div class="fl_l" style="margin-left: 10px">
      <input type="hidden" id="c[city_id]" name="c[city_id]">
    </div>
    <div class="fl_l" style="margin-left: 10px">
      <input type="hidden" id="c[role]" name="c[role]">
    </div>
  </div>
  <div class="summary_wrap">
    <?php $this->renderPartial('//layouts/pages', array(
      'url' => '/users/feedback/index'. ((sizeof($url_suffix) > 0) ? '?'. implode('&', $url_suffix) : ''),
      'offset' => $offset,
      'offsets' => $offsets,
      'delta' => $delta,
    )) ?>
    <div class="fl_r progress"></div>
    <div class="summary"><?php echo Yii::t('app', '{n} сообщение|{n} сообщения|{n} сообщений', $offsets) ?></div>
  </div>

  <div class="results_wrap">
    <table class="users_table">
      <thead>
      <tr>
        <th>#</th>
        <th>Контакт</th>
        <th>Имя</th>
        <th>Город</th>
        <th>Дата/время</th>
        <th>Сообщение</th>
        <th></th>
      </tr>
      </thead>
      <tbody rel="pagination">
      <?php echo $this->renderPartial('_feedbacklist', array('feedbacks' => $feedbacks, 'offset' => $offset)) ?>
      </tbody>
    </table>
  </div>

  <script type="text/javascript">
    var rolesList = [<?php echo (isset($rolesJs)) ? implode(',', $rolesJs) : '' ?>];
  </script>

<?php
$this->pageJS = <<<HTML
Users.initFeedback();
HTML;

