<?php
/**
 * Created by PhpStorm.
 * User: Sum
 * Date: 18.02.14
 * Time: 12:10
 */

Yii::app()->getClientScript()->registerCssFile('/css/register.css');
Yii::app()->getClientScript()->registerScriptFile('/js/register.js');

$citiesList = City::getDataArray();
$citiesJS = array();

foreach ($citiesList as $city_name => $city_id) {
  $citiesJS[] = "[". $city_id .",'". $city_name ."']";
}
$citiesJS = implode(",", $citiesJS);
?>

<div class="register_block">
  <div id="register_result"></div>
  <div id="register_error" class="error"></div>
  <div class="clear_fix">
    <?php echo ActiveHtml::textField('email', '', array('class' => 'text', 'placeholder' => 'E-Mail')) ?>
  </div>
  <div class="clear_fix">
    <?php echo ActiveHtml::passwordField('password', '', array('class' => 'text', 'placeholder' => 'Пароль')) ?>
  </div>
  <div class="clear_fix">
    <?php echo ActiveHtml::textField('firstname', '', array('class' => 'text', 'placeholder' => 'Имя')) ?>
  </div>
  <div class="clear_fix">
    <?php echo ActiveHtml::textField('lastname', '', array('class' => 'text', 'placeholder' => 'Фамилия')) ?>
  </div>
  <div class="clear_fix">
    <?php echo ActiveHtml::hiddenField('gender', '') ?>
  </div>
  <div class="clear_fix">
    <?php echo ActiveHtml::hiddenField('city_id', '') ?>
  </div>
  <div class="button_blue button_wide button_big clear_fix">
    <button id="register_button" onclick="Register.submit()">Зарегистрироваться</button>
  </div>
</div>

<?php
$this->pageJS = <<<HTML
Register.init({cities: [$citiesJS]});
HTML;
