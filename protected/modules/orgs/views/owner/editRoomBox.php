<?php
/** @var Room $room
 * @var ActiveForm $form
 */

Yii::app()->getClientScript()->registerCssFile('/css/orgs.css');
Yii::app()->getClientScript()->registerScriptFile('/js/orgs.js');

$this->pageTitle = 'Редактирование помещения';
?>
<div class="edit_room_content">
  <div id="room_result"></div>
  <div id="room_error" class="error"></div>
  <div class="edit_room_general_row clear_fix">
    <div class="edit_room_general_label fl_l ta_r">Название:</div>
    <div class="edit_room_general_labeled fl_l"><?php echo ActiveHtml::textField('name', $room->name, array('class' => 'text')) ?></div>
  </div>
</div>
<?php
$this->pageJS = <<<HTML
HTML;
