<?php /** @var $param AdvertParam */ ?>
<?php foreach ($params as $param): ?>
  <tr>
    <td><?php echo $param->title ?></td>
    <td><?php echo $param->category->name ?></td>
    <td>
      <a onclick="AdvertParam.edit('<?php echo $param->param_id ?>')">Редактировать</a> |
      <a onclick="AdvertParam.delete('<?php echo $param->param_id ?>')">Удалить</a>
    </td>
  </tr>
<?php endforeach; ?>