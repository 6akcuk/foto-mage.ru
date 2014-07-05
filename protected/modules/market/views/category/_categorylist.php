<?php /** @var $category MarketCategory */ ?>
<?php foreach ($categories as $category): ?>
  <tr>
    <td><?php echo $category->name ?></td>
    <td><?php echo ($category->parent) ? $category->parent->name : '' ?></td>
    <td>
      <a onclick="MarketCategory.edit('<?php echo $category->category_id ?>')">Редактировать</a> |
      <a onclick="MarketCategory.delete('<?php echo $category->category_id ?>')">Удалить</a>
    </td>
  </tr>
<?php endforeach; ?>