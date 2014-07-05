<?php
/**
 * @var News $post
 * @var ActiveForm $form
 */

Yii::app()->getClientScript()->registerCssFile('/css/cities.css');
Yii::app()->getClientScript()->registerScriptFile('/js/cities.js');

$timezonesJs = array();

foreach (Yii::app()->getModule('unify')->timezones as $timezone => $name) {
  $timezonesJs[] = "['". $timezone ."','". $name ."']";
}

$timezonesJs = implode(",", $timezonesJs);

$this->pageTitle = 'Добавить город';

$this->renderPartial('_form', array('city' => $city));

$this->pageJS = <<<HTML
City.initForm({
  timezones: [$timezonesJs]
});
HTML;
