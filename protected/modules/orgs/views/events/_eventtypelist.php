<?php /** @var $type EventType */ ?>
<?php foreach ($eventTypes as $type): ?>
  <tr>
    <td><?php echo $type->type_name ?></td>
    <td>
      <a onclick="OrgEventType.edit('<?php echo $type->type_id ?>')">Редактировать</a> |
      <a onclick="OrgEventType.delete('<?php echo $type->type_id ?>')">Удалить</a>
    </td>
  </tr>
<?php endforeach; ?>