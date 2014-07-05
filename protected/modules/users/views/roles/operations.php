<?php
$this->pageTitle = Yii::app()->name . ' - Операции';

Yii::app()->getClientScript()->registerScriptFile('/js/users.js');
Yii::app()->getClientScript()->registerCssFile('/css/users.css');

$delta = Yii::app()->getModule('users')->operationsPerPage;

$url_suffix = array();
if (sizeof($c) > 0) {
  foreach ($c as $c_key => $c_value) {
    $url_suffix[] = "c[". $c_key ."]=". urlencode($c_value);
  }
}
?>

<div class="users_search_wrap clear_fix">
  <div rel="filters" class="fl_l">
    <?php echo ActiveHtml::textField('c[name]', (isset($c['name'])) ? $c['name'] : '', array('class' => 'text', 'placeholder' => 'Код операции')) ?>
  </div>
  <div class="fl_r">
    <div class="button_blue">
      <button onclick="Users.addOperation()">Добавить операцию</button>
    </div>
  </div>
</div>
<div class="summary_wrap">
  <?php $this->renderPartial('//layouts/pages', array(
    'url' => '/users/roles/operations'. ((sizeof($url_suffix) > 0) ? '?'. implode('&', $url_suffix) : ''),
    'offset' => $offset,
    'offsets' => $offsets,
    'delta' => $delta,
  )) ?>
  <div class="fl_r progress"></div>
  <div class="summary"><?php echo Yii::t('app', '{n} операция|{n} операции|{n} операций', $offsets) ?></div>
</div>

<div class="results_wrap">
  <table class="users_table">
    <thead>
    <tr>
      <th>Имя</th>
      <th>Описание</th>
      <th>Действия</th>
    </tr>
    </thead>
    <tbody rel="pagination">
    <?php echo $this->renderPartial('_operationlist', array('operations' => $operations, 'offset' => $offset)) ?>
    </tbody>
  </table>
</div>

<?php
$this->pageJS = <<<HTML
Users.initOperations();
HTML;
