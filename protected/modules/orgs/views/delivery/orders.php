<?php
$this->pageTitle = Yii::app()->name . ' - '. $org->name;

Yii::app()->getClientScript()->registerCssFile('/css/page.css');
Yii::app()->getClientScript()->registerCssFile('/css/delivery.css');
Yii::app()->getClientScript()->registerScriptFile('/js/delivery.js');

$delta = Yii::app()->getModule('orgs')->deliveryOrdersPerPage;
?>
  <div id="header" class="clear_fix">
    <a href="/org<?php echo $org->org_id ?>" onclick="return nav.go(this, event)"><?php echo $org->name ?></a> &raquo;
    <a href="/orgs/delivery/index?id=<?php echo $org->org_id ?>" onclick="return nav.go(this, event)">Доставка</a> &raquo;
    Заказы
  </div>
  <div class="summary_wrap clear_fix">
    <?php $this->renderPartial('//layouts/pages', array(
      'url' => '/orgs/delivery/orders?id='. $org->org_id,
      'offset' => $offset,
      'offsets' => $offsets,
      'delta' => $delta,
    )) ?>
    <div class="summary"><?php echo Yii::t('app', '{n} заказ|{n} заказа|{n} заказов', $offsets) ?></div>
  </div>

  <div id="public" class="clear_fix">
    <?php if ($orders): ?>
      <?php $this->renderPartial('_orderlistshort', array('orders' => $orders)) ?>
    <?php else: ?>
      <div id="no_results">Здесь будут отображаться заказы, которые оформили наши пользователи.</div>
    <?php endif; ?>
  </div>
<?php
$this->pageJS = <<<HTML

HTML;
