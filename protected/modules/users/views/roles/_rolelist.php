<?php /** @var $user User */ ?>
<?php $page = ($offset + Yii::app()->getModule('users')->rolesPerPage) / Yii::app()->getModule('users')->rolesPerPage ?>
<?php $added = false; ?>
<?php foreach ($roles as $role): ?>
  <tr<?php if(!$added) { echo ' rel="page-'. $page .'"'; $added = true; } ?>>
    <td><?php echo $role->name ?></td>
    <td><?php echo $role->description ?></td>
    <td>
      <a onclick="Users.editRole('<?php echo $role->name ?>')">Редактировать</a> |
      <a onclick="Users.deleteRole('<?php echo $role->name ?>')">Удалить</a>
    </td>
  </tr>
<?php endforeach; ?>