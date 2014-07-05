<?php
/**
 * @var User $user
 * @var Profile $profile
 */

$rolesJs = array();
foreach ($roles as $role) {
  if (Yii::app()->user->model->role->itemname != "Администратор" && $role->name == "Администратор") continue;
  $rolesJs[] = "'". $role->name ."'";
}
$rolesJs = implode(",", $rolesJs);

$citiesList =
  (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City'))
    ? array(Yii::app()->user->model->profile->city->name => Yii::app()->user->model->profile->city_id)
    : City::getDataArray();

$citiesJS = array();
foreach ($citiesList as $city_name => $city_id) {
  $citiesJS[] = "[". $city_id .",'". $city_name ."']";
}
$citiesJS = implode(",", $citiesJS);

$this->pageTitle = 'Редактирование пользователя';
?>
  <div class="edit_user_content">
    <div id="edit_user_result"></div>
    <div id="edit_user_error" class="error"></div>
    <div class="edit_user_general_row clear_fix">
      <div class="edit_user_general_label fl_l ta_r"><?php echo $user->getAttributeLabel('email') ?>:</div>
      <div class="edit_user_general_labeled fl_l"><?php echo ActiveHtml::textField('edit_user_email', $user->email, array('class' => 'text')) ?></div>
    </div>
    <div class="edit_user_general_row clear_fix">
      <div class="edit_user_general_label fl_l ta_r"><?php echo $user->getAttributeLabel('login') ?>:</div>
      <div class="edit_user_general_labeled fl_l"><?php echo ActiveHtml::textField('edit_user_login', $user->login, array('class' => 'text')) ?></div>
    </div>
    <div class="edit_user_general_row clear_fix">
      <div class="edit_user_general_label fl_l ta_r"><?php echo $user->getAttributeLabel('password') ?>:</div>
      <div class="edit_user_general_labeled fl_l"><?php echo ActiveHtml::passwordField('edit_user_password', $user->password, array('class' => 'text')) ?></div>
    </div>
    <div class="edit_user_general_row clear_fix">
      <div class="edit_user_general_label fl_l ta_r">Роль пользователя:</div>
      <div class="edit_user_general_labeled fl_l"><?php echo ActiveHtml::hiddenField('edit_user_role', ($user->role) ? $user->role->itemname : '') ?></div>
    </div>
    <div class="edit_user_general_row clear_fix">
      <div class="edit_user_general_label fl_l ta_r">Город пользователя:</div>
      <div class="edit_user_general_labeled fl_l"><?php echo ActiveHtml::hiddenField('edit_user_city_id', ($profile->city_id) ? $profile->city_id : '') ?></div>
    </div>
    <div class="edit_user_general_row clear_fix">
      <div class="edit_user_general_label fl_l ta_r"><?php echo $profile->getAttributeLabel('lastname') ?>:</div>
      <div class="edit_user_general_labeled fl_l"><?php echo ActiveHtml::textField('edit_user_lastname', $profile->lastname, array('class' => 'text')) ?></div>
    </div>
    <div class="edit_user_general_row clear_fix">
      <div class="edit_user_general_label fl_l ta_r"><?php echo $profile->getAttributeLabel('firstname') ?>:</div>
      <div class="edit_user_general_labeled fl_l"><?php echo ActiveHtml::textField('edit_user_firstname', $profile->firstname, array('class' => 'text')) ?></div>
    </div>
  </div>
<?php
$this->pageJS = <<<HTML
cur.uiRolesDD = new Dropdown('edit_user_role', {
  width: 190,
  label: 'Выберите роль',
  items: [$rolesJs]
});
cur.uiCitiesDD = new Dropdown('edit_user_city_id', {
  width: 190,
  label: 'Выберите город',
  items: [$citiesJS]
});
HTML;
