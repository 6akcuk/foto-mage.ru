<?php /** @var $element DeliveryMenuElement */ ?>
<?php foreach ($elements as $element): ?>
<div class="fl_l delivery_menu_element">
  <div class="delivery_menu_icon">
    <?php echo ActiveHtml::showUploadImage($element->icon, 'b') ?>
  </div>
  <div class="delivery_menu_name"><?php echo $element->name ?></div>
  <div class="delivery_menu_bottom">
    <a onclick="Delivery.editMenuElement(<?php echo $element->element_id ?>)">ред.</a> |
    <a onclick="Delivery.deleteMenuElement(<?php echo $element->element_id ?>)">удал.</a>
  </div>
</div>
<?php endforeach; ?>