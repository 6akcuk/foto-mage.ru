<?php
$this->pageTitle=Yii::app()->name . ' - Логи ботов';

Yii::app()->getClientScript()->registerCssFile('/css/users.css');

$delta = Yii::app()->controller->module->parserLogsPerPage;

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
  </div>
  <div class="summary_wrap">
    <?php $this->renderPartial('//layouts/pages', array(
      'url' => '/unify/parserLog/index'. ((sizeof($url_suffix) > 0) ? '?'. implode('&', $url_suffix) : ''),
      'offset' => $offset,
      'offsets' => $offsets,
      'delta' => $delta,
    )) ?>
    <div class="fl_r progress"></div>
    <div class="summary"><?php echo Yii::t('app', '{n} сообщение|{n} сообщения|{n} сообщений', $offsets) ?></div>
  </div>

  <div class="results_wrap">
    <table class="data_table">
      <thead>
      <tr>
        <th>#</th>
        <th>Время начала</th>
        <th>Время окончания</th>
        <th>Сообщение</th>
      </tr>
      </thead>
      <tbody rel="pagination">
      <?php echo $this->renderPartial('_loglist', array('logs' => $logs, 'offset' => $offset)) ?>
      </tbody>
    </table>
  </div>

<?php
$this->pageJS = <<<HTML
HTML;

