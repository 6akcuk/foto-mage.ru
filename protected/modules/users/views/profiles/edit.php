<?php
/** @var $userinfo User */

Yii::app()->getClientScript()->registerCssFile('/css/profile.css');
Yii::app()->getClientScript()->registerScriptFile('/js/profile.js');

Yii::app()->getClientScript()->registerCssFile('/css/upload.css');
Yii::app()->getClientScript()->registerScriptFile('/js/upload.js');

$this->pageTitle = Yii::app()->name .' - Редактирование профиля';

$citiesList = City::getDataArray();
$citiesJS = array();

foreach ($citiesList as $city_name => $city_id) {
  $citiesJS[] = "[". $city_id .",'". $city_name ."']";
}
$citiesJS = implode(",", $citiesJS);
?>
<div class="profile_edit_content">
  <div id="profile_edit_result"></div>
  <div id="profile_edit_error" class="error"></div>
  <div class="profile_edit_row clear_fix">
    <div class="fl_l ta_r edit_label"><?php echo $userinfo->profile->getAttributeLabel('firstname') ?>:</div>
    <div class="fl_l edit_wrap">
      <?php echo ActiveHtml::textField('firstname', $userinfo->profile->firstname, array('class' => 'text text_big')) ?>
    </div>
  </div>
  <div class="profile_edit_row clear_fix">
    <div class="fl_l ta_r edit_label"><?php echo $userinfo->profile->getAttributeLabel('lastname') ?>:</div>
    <div class="fl_l edit_wrap">
      <?php echo ActiveHtml::textField('lastname', $userinfo->profile->lastname, array('class' => 'text text_big')) ?>
    </div>
  </div>
  <div class="profile_edit_row clear_fix">
    <div class="fl_l ta_r edit_label"><?php echo $userinfo->profile->getAttributeLabel('gender') ?>:</div>
    <div class="fl_l edit_wrap">
      <?php echo ActiveHtml::hiddenField('gender', $userinfo->profile->gender) ?>
    </div>
  </div>
  <div class="profile_edit_row clear_fix">
    <div class="fl_l ta_r edit_label"><?php echo $userinfo->profile->getAttributeLabel('city_id') ?>:</div>
    <div class="fl_l edit_wrap">
      <?php echo ActiveHtml::hiddenField('city_id', $userinfo->profile->city_id) ?>
    </div>
  </div>
  <div class="profile_edit_row clear_fix">
    <div class="fl_l ta_r edit_label"><?php echo $userinfo->profile->getAttributeLabel('about') ?>:</div>
    <div class="fl_l edit_wrap">
      <?php echo ActiveHtml::textArea('about', $userinfo->profile->about, array('class' => 'text text_big')) ?>
    </div>
  </div>
  <div class="profile_edit_row clear_fix">
    <div class="fl_l ta_r edit_label">Фотография:</div>
    <div class="fl_l edit_wrap">
      <?php echo ActiveHtml::hiddenField('photo', $userinfo->profile->photo) ?>
    </div>
  </div>
  <div class="clear_fix">
    <div class="fl_l ta_r edit_label"></div>
    <div class="fl_l edit_wrap">
      <div class="button_blue">
        <button onclick="Profile.saveEdit(this)">Сохранить</button>
      </div>
    </div>
  </div>
</div>

<?php
$this->pageJS = <<<HTML
Profile.initEditForm({
  uploadAction: 'http://cs1.foto-mage.ru/upload.php',
  cities: [$citiesJS]
});
HTML;
