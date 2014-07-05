<?php
/** @var Organization $org
 * @var ActiveForm $form
 */

Yii::app()->getClientScript()->registerCssFile('/css/orgs.css');
Yii::app()->getClientScript()->registerScriptFile('/js/orgs.js');

$this->pageTitle = 'Добавить новое помещение';
?>
<div class="add_room_content">
  <div id="room_result"></div>
  <div id="room_error" class="error"></div>
  <div class="add_room_general_row clear_fix">
    <div class="add_room_general_label fl_l ta_r">Название:</div>
    <div class="add_room_general_labeled fl_l"><?php echo ActiveHtml::textField('name', '', array('class' => 'text')) ?></div>
  </div>
</div>
<?php
$this->pageJS = <<<HTML
HTML;
