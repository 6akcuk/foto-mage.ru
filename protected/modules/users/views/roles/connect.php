<?php
$this->pageTitle = Yii::app()->name . ' - Связи';

Yii::app()->getClientScript()->registerScriptFile('/js/users.js');
Yii::app()->getClientScript()->registerCssFile('/css/users.css');

Yii::app()->getClientScript()->registerScriptFile('/js/scrollbar.js');

/** @var RbacItem $role */
?>
<div class="roles_connect_wrap">
  <div id="roles_list">
  <?php foreach ($roles as $role): ?>
    <a onclick="Users.connectRole('<?php echo $role->name ?>')" class="role_connect clear_fix">
      <div class="role_connect_help fl_r">Редактировать связи</div>
      <div class="role_connect_name"><?php echo $role->name ?></div>
      <div class="role_connect_description"><?php echo $role->description ?></div>
    </a>
  <?php endforeach; ?>
  </div>
</div>