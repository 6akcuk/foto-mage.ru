<?php
$this->pageTitle = Yii::app()->name . ' - Настраиваемые параметры';

Yii::app()->getClientScript()->registerCssFile('/css/advert.css');
Yii::app()->getClientScript()->registerScriptFile('/js/advert_params.js');

$delta = Yii::app()->getModule('advert')->advertParamsPerPage;
?>
  <div class="advert_search_wrap clear_fix">
    <div rel="filters" class="fl_l">
      <?php echo ActiveHtml::textField('c[title]', (isset($c['title'])) ? $c['title'] : '', array('class' => 'text', 'placeholder' => 'Начните вводить название параметра')) ?>
    </div>
    <div class="fl_r">
      <div class="button_blue">
        <button onclick="AdvertParam.add()">Добавить параметр</button>
      </div>
    </div>
  </div>
  <div class="summary_wrap">
    <?php $this->renderPartial('//layouts/pages', array(
      'url' => '/advert/param/index',
      'offset' => $offset,
      'offsets' => $offsets,
      'delta' => $delta,
    )) ?>
    <div class="summary"><?php echo Yii::t('app', '{n} параметр|{n} параметра|{n} параметров', $offsets) ?></div>
  </div>

  <div class="results_wrap">
    <table class="advert_table">
      <thead>
      <tr>
        <th width="">Название</th>
        <th>Категория</th>
        <th>Действия</th>
      </tr>
      </thead>
      <tbody rel="pagination">
      <?php echo $this->renderPartial('_paramlist', array('params' => $params, 'offset' => $offset)) ?>
      </tbody>
    </table>
  </div>
<?php
$this->pageJS = <<<HTML
AdvertParam.init();
HTML;
