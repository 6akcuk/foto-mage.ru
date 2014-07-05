<?php /** @var $user User */ ?>
<?php $page = ($offset + Yii::app()->controller->module->usersPerPage) / Yii::app()->controller->module->usersPerPage ?>
<?php $added = false; ?>
<?php foreach ($users as $row_idx => $user): ?>
<tr<?php if ($row_idx % 2) echo ' class="even"' ?>>
  <td><?php echo $user->id ?></td>
  <td><?php echo $user->email ?></td>
  <td><?php echo $user->getDisplayName() ?></td>
  <td><?php echo ($user->profile->city) ? $user->profile->city->name : '' ?></td>
  <td style="position: relative">
    <a onclick="Users.assignRole(this, <?php echo $user->id ?>)">
      <?php echo ($user->role) ? $user->role->itemname : "Назначить роль" ?>
    </a>
  </td>
  <td>
    <a class="data_edit" onmouseover="fadeIn(this, 'Редактировать пользователя')" onmouseout="fadeOut(this, 0.4)" onclick="Users.editUser('<?php echo $user->id ?>')">Редактировать</a>
    <a class="data_delete" onmouseover="fadeIn(this, 'Удалить пользователя')" onmouseout="fadeOut(this, 0.4)" onclick="Users.deleteUser('<?php echo $user->id ?>')">Удалить</a>
    <a class="data_orgs" onmouseover="fadeIn(this, 'Организации пользователя')" onmouseout="fadeOut(this, 0.4)" onclick="Users.linkOrg('<?php echo $user->id ?>')">Организации</a>
  </td>
</tr>
<?php endforeach; ?>