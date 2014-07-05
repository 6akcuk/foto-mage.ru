<?php /** @var $code DiscountPromoCode */ ?>
<?php foreach ($codes as $code): ?>
  <tr>
    <td><?php echo $code->value ?></td>
    <td><?php echo $code->owner->email .' / '. $code->owner->getDisplayName() ?></td>
    <td><?php echo ActiveHtml::date($code->add_date) ?></td>
  </tr>
<?php endforeach; ?>