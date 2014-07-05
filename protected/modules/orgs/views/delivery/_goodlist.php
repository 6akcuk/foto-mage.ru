<?php /** @var $good DeliveryGood */ ?>
<?php foreach ($goods as $good): ?>
  <div id="delivery_good<?php echo $good->good_id ?>" class="delivery_good clear_fix">
    <div class="fl_l delivery_good_photo">
      <?php echo ActiveHtml::showUploadImage($good->facephoto, 'b') ?>
      <div class="delivery_good_price"><?php echo ActiveHtml::price($good->price, $good->currency) ?></div>
    </div>
    <div class="fl_l delivery_good_info_wrap">
      <a class="delivery_good_title"><b><?php echo $good->name ?></b></a>
      <div class="delivery_good_category"><?php echo $good->element->category->name ?></div>
      <div class="delivery_good_element"><?php echo $good->element->name ?></div>
      <div class="delivery_good_text">
        <div class="delivery_good_fullstory"><?php echo nl2br($good->shortstory) ?></div>
      </div>
      <div class="delivery_good_bottom">
        <a onclick="Delivery.editGood(<?php echo $good->good_id ?>)">Редактировать</a> |
        <a onclick="Delivery.deleteGood(<?php echo $good->good_id ?>)">Удалить</a>
      </div>
    </div>
  </div>
<?php endforeach; ?>