<?php
$this->pageTitle = Yii::app()->name . ' - Дисконтная система';

Yii::app()->getClientScript()->registerCssFile('/css/orgs.css');
Yii::app()->getClientScript()->registerScriptFile('/js/discount.js');

$delta = Yii::app()->getModule('orgs')->discountActionsPerPage;
?>
  <div id="header">
    <a href="/org<?php echo $org->org_id ?>" onclick="return nav.go(this, event)"><?php echo $org->name ?></a> &raquo;
    Дисконтная система
  </div>
  <div class="orgs_search_wrap clear_fix">
    <div rel="filters" class="fl_l">
    </div>
    <div class="fl_r">
      <div class="button_blue">
        <button onclick="Discount.giveCard(<?php echo $org->org_id ?>)">Выдать дисконтную карту</button>
      </div>
      <div class="button_blue">
        <button onclick="Discount.addAction(<?php echo $org->org_id ?>)">Добавить акцию</button>
      </div>
    </div>
  </div>
  <div class="summary_wrap clear_fix">
    <?php $this->renderPartial('//layouts/pages', array(
      'url' => '/orgs/discount/index',
      'offset' => $offset,
      'offsets' => $offsets,
      'delta' => $delta,
    )) ?>
    <div class="summary"><?php echo Yii::t('app', '{n} акция|{n} акции|{n} акций', $offsets) ?></div>
  </div>

  <div class="results_wrap">
  <?php if ($actions): ?>
    <?php $this->renderPartial('_actionlist', array('actions' => $actions)) ?>
  <?php else: ?>
    <div id="no_results">Здесь будут отображаться все акции, которые проводятся или проводились</div>
  <?php endif; ?>
  </div>
<?php
$this->pageJS = <<<HTML
Discount.init();
HTML;
