<?php /** @var $user User */ ?>
<?php $page = ($offset + Yii::app()->getModule('users')->operationsPerPage) / Yii::app()->getModule('users')->operationsPerPage ?>
<?php $added = false; ?>
<?php foreach ($operations as $operation): ?>
  <tr<?php if(!$added) { echo ' rel="page-'. $page .'"'; $added = true; } ?>>
    <td><?php echo $operation->name ?></td>
    <td><?php echo $operation->description ?></td>
    <td>
      <a onclick="Users.editOperation('<?php echo $operation->name ?>')">Редактировать</a> |
      <a onclick="Users.deleteOperation('<?php echo $operation->name ?>')">Удалить</a>
    </td>
  </tr>
<?php endforeach; ?>