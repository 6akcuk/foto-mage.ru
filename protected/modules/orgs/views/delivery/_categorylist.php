<?php /** @var $category DeliveryCategory */ ?>
<?php foreach ($categories as $category): ?>
<tr>
  <td><?php echo $category->name ?></td>
  <td>
    <a onclick="DeliveryCategory.edit('<?php echo $category->category_id ?>')">Редактировать</a> |
    <a onclick="DeliveryCategory.delete('<?php echo $category->category_id ?>')">Удалить</a>
  </td>
</tr>
<?php endforeach; ?>