<?php
/** @var Organization $org
 * @var ActiveForm $form
 * @var Room $room
 */

Yii::app()->getClientScript()->registerCssFile('/css/orgs.css');
Yii::app()->getClientScript()->registerScriptFile('/js/orgs.js');

$this->pageTitle = 'Помещения';
?>
<div class="clear_fix org_rooms_list">
  <?php foreach ($org->rooms as $room): ?>
  <div id="room<?php echo $room->room_id ?>" class="fl_l cell clear_fix">
    <div class="row fl_l">
      <div class="image">
        <img src="/images/camera_c.gif" />
      </div>
    </div>
    <div class="info fl_l">
      <div class="name"><?php echo $room->name ?></div>
    </div>
    <div class="edit_actions fl_l">
      <a class="choose" onclick="Org.deleteRoom(<?php echo $room->room_id ?>)">Удалить помещение</a>
      <a class="choose" onclick="Org.editRoom(<?php echo $room->room_id ?>)">Редактировать</a>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php
$this->pageJS = <<<HTML
HTML;
