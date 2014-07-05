<?php /** @var $order DeliveryOrder */ ?>
<?php foreach ($orders as $order): ?>
  <a href="/orgs/delivery/order?id=<?php echo $order->order_id ?>" onclick="return nav.go(this, event)" id="delivery_order<?php echo $order->order_id ?>" class="delivery_order_short">
    <div>
      <span class="delivery_order_title">Заказ №<?php echo $order->order_id ?> (Сумма заказа <?php echo ActiveHtml::price($order->summary, $order->currency) ?>)</span>
    </div>
    <small>
      <?php echo Yii::t('app', '{n} товар|{n} товара|{n} товаров', sizeof($order->items)) ?>,
      <?php echo ActiveHtml::date($order->add_date) ?>
      &mdash;
      заказ <?php echo Yii::t('app', $order->status) ?>
    </small>
  </a>
<?php endforeach; ?>