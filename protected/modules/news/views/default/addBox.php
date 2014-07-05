<?php
/**
 * @var News $post
 * @var ActiveForm $form
 */

Yii::app()->getClientScript()->registerCssFile('/css/news.css');
Yii::app()->getClientScript()->registerScriptFile('/js/news.js');

Yii::app()->getClientScript()->registerCssFile('/css/upload.css');
Yii::app()->getClientScript()->registerScriptFile('/js/upload.js');

if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
  $post->city_id = Yii::app()->user->model->profile->city_id;
}

$citiesList =
  (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City'))
    ? array(Yii::app()->user->model->profile->city->name => Yii::app()->user->model->profile->city_id)
    : City::getDataArray();
$citiesJs = array();

foreach ($citiesList as $city_name => $city_id) {
  $citiesJs[] = "[". $city_id .",'". $city_name ."']";
}
$citiesJs = implode(",", $citiesJs);

$this->pageTitle = 'Добавить новость';

$this->renderPartial('_form', array('post' => $post));

$this->pageJS = <<<HTML
cur.uploadAction = 'http://cs1.e-bash.me/upload.php';

News.initForm({
  cities: [$citiesJs]
});
HTML;
