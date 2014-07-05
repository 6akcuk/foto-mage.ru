<?php
$this->pageTitle = Yii::app()->name . ' - Дисконтная система';

Yii::app()->getClientScript()->registerCssFile('/css/orgs.css');
Yii::app()->getClientScript()->registerScriptFile('/js/discount.js');

$delta = Yii::app()->getModule('orgs')->discountActionsPerPage;
?>
  <div id="header">
    <a href="/org<?php echo $org->org_id ?>" onclick="return nav.go(this, event)"><?php echo $org->name ?></a> &raquo;
    <a href="/orgs/discount/index?id=<?php echo $org->org_id ?>" onclick="return nav.go(this, event)">Дисконтная система</a> &raquo;
    Просмотр промо-кодов
  </div>
  <div class="orgs_search_wrap clear_fix">
    <div rel="filters" class="fl_l">
      <?php echo ActiveHtml::textField('c[value]', (isset($c['value'])) ? $c['value'] : '', array('class' => 'text', 'placeholder' => 'Начните вводить промо-код')) ?>
    </div>
  </div>
  <div class="summary_wrap clear_fix">
    <?php $this->renderPartial('//layouts/pages', array(
      'url' => '/orgs/discount/codes',
      'offset' => $offset,
      'offsets' => $offsets,
      'delta' => $delta,
    )) ?>
    <div class="summary"><?php echo Yii::t('app', 'Выдан {n} промо-код|Выдано {n} промо-кода|Выдано {n} промо-кодов', $offsets) ?></div>
  </div>

  <div class="results_wrap">
    <?php if ($codes): ?>
    <table class="orgs_table">
      <tr>
        <th>Промо-код</th>
        <th>Владелец</th>
        <th>Дата получения</th>
      </tr>
      <?php $this->renderPartial('_codelist', array('codes' => $codes)) ?>
    </table>
    <?php else: ?>
      <div id="no_results">Здесь будут отображаться все промо-коды, которые были выданы пользователям</div>
    <?php endif; ?>
  </div>
<?php
$this->pageJS = <<<HTML
Discount.initCodes();
HTML;
