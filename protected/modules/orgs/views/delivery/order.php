<?php
/**
 * @var DeliveryOrder $order
 */
$this->pageTitle = Yii::app()->name . ' - '. $org->name;

Yii::app()->getClientScript()->registerCssFile('/css/page.css');
Yii::app()->getClientScript()->registerCssFile('/css/delivery.css');
Yii::app()->getClientScript()->registerScriptFile('/js/delivery.js');

$delta = Yii::app()->getModule('orgs')->deliveryOrdersPerPage;
?>
  <div id="header" class="clear_fix">
  <a href="/org<?php echo $org->org_id ?>" onclick="return nav.go(this, event)"><?php echo $org->name ?></a> &raquo;
    <a href="/orgs/delivery/index?id=<?php echo $org->org_id ?>" onclick="return nav.go(this, event)">Доставка</a> &raquo;
    <a href="/orgs/delivery/orders?id=<?php echo $org->org_id ?>" onclick="return nav.go(this, event)">Заказы</a> &raquo;
    Заказ №<?php echo $order->order_id ?>
  </div>
  <div id="public">
    <div class="page_info">
      <div class="clear_fix">
        <div class="label fl_l">Сумма заказа:</div>
        <div class="labeled fl_l"><?php echo ActiveHtml::price($order->summary, $order->currency) ?></div>
      </div>
      <div class="clear_fix">
        <div class="label fl_l">Адрес доставки:</div>
        <div class="labeled fl_l"><?php echo $order->address ?></div>
      </div>
      <div class="clear_fix">
        <div class="label fl_l">Количество персон:</div>
        <div class="labeled fl_l"><?php echo $order->persons_num ?></div>
      </div>
      <div class="clear_fix">
        <div class="label fl_l">Контактный телефон:</div>
        <div class="labeled fl_l"><?php echo $order->phone ?></div>
      </div>
      <div class="clear_fix">
        <div class="label fl_l">Дополнительная информация:</div>
        <div class="labeled fl_l"><?php echo $order->additional ?></div>
      </div>
    </div>
    <div class="p_header_bottom"><?php echo Yii::t('app', '{n} товар|{n} товара|{n} товаров', sizeof($order->items)) ?></div>
    <?php $this->renderPartial('_orderitemlist', array('items' => $order->items)) ?>
  </div>
<?php
$this->pageJS = <<<HTML

HTML;
