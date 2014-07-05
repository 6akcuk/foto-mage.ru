<?php /** @var $item DeliveryOrderItem */ ?>
<table width="100%">
  <tr>
    <th>Изображение</th>
    <th>Наименование товара</th>
    <th>Количество</th>
    <th>Цена одного</th>
    <th>Общая стоимость</th>
  </tr>
<?php foreach ($items as $item): ?>
  <tr>
    <td class="delivery_order_item_photo">
      <?php echo ActiveHtml::showUploadImage($item->good_photo, 'b') ?>
    </td>
    <td class="delivery_order_item_name">
      <?php echo $item->good_name ?>
    </td>
    <td>
      <?php echo $item->amount ?>
    </td>
    <td>
      <?php echo ActiveHtml::price($item->price, $item->currency) ?>
    </td>
    <td>
      <?php echo ActiveHtml::price($item->price * $item->amount, $item->currency) ?>
    </td>
  </tr>
<?php endforeach; ?>
</table>