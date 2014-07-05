<?php
$this->pageTitle = Yii::app()->name . ' - '. $org->name;

Yii::app()->getClientScript()->registerCssFile('/css/page.css');
Yii::app()->getClientScript()->registerCssFile('/css/delivery.css');
Yii::app()->getClientScript()->registerScriptFile('/js/delivery.js');

$delta = Yii::app()->getModule('orgs')->deliveryGoodsPerPage;
?>
  <div id="header" class="clear_fix">
    <a href="/org<?php echo $org->org_id ?>" onclick="return nav.go(this, event)"><?php echo $org->name ?></a> &raquo;
    <a href="/orgs/delivery/index?id=<?php echo $org->org_id ?>" onclick="return nav.go(this, event)">Доставка</a> &raquo;
    Товары доставки
    <a onclick="Delivery.addGood(<?php echo $org->org_id ?>)" class="fl_r header_right_link">Добавить товар</a>
  </div>
  <div class="summary_wrap clear_fix">
    <?php $this->renderPartial('//layouts/pages', array(
      'url' => '/orgs/delivery/goods?id='. $org->org_id,
      'offset' => $offset,
      'offsets' => $offsets,
      'delta' => $delta,
    )) ?>
    <div class="summary"><?php echo Yii::t('app', '{n} товар|{n} товара|{n} товаров', $offsets) ?></div>
  </div>

  <div id="public" class="clear_fix">
    <?php if ($goods): ?>
      <?php $this->renderPartial('_goodlist', array('goods' => $goods)) ?>
    <?php else: ?>
      <div id="no_results">Товары в доставку еще не добавлены. Начните с нажатия по ссылке в правом верхнем углу.</div>
    <?php endif; ?>
  </div>
<?php
$this->pageJS = <<<HTML

HTML;
