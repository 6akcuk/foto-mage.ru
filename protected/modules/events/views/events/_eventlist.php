<?php /** @var $event Event */ ?>
<?php foreach ($events as $event): ?>
  <tr>
    <td><?php echo $event->event_type->type_name ?></td>
    <td><?php echo $event->title ?></td>
    <td><?php echo $event->org->name ?></td>
    <td>
      <a onclick="Event.edit('<?php echo $event->event_id ?>')">Редактировать</a> |
      <a onclick="Event.delete('<?php echo $event->event_id ?>')">Удалить</a>
    </td>
  </tr>
<?php endforeach; ?>