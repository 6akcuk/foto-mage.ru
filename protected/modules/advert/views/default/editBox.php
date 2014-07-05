<?php
/**
 * @var AdvertParam $param
 * @var AdvertCategory $category
 * @var ActiveForm $form
 */

Yii::app()->getClientScript()->registerCssFile('/css/advert.css');
Yii::app()->getClientScript()->registerScriptFile('/js/advert.js');

Yii::app()->getClientScript()->registerCssFile('/css/upload.css');
Yii::app()->getClientScript()->registerScriptFile('/js/upload.js');

$categoriesJson = array();
$categoriesJs = array();
foreach ($categories as $category) {
  $categoriesJs[] = "[". $category->category_id .",'". $category->name ."',true,true]";
  foreach ($category->childs as $child) {
    $categoriesJson[$child->category_id] = array('no_title' => intval($child->no_title), 'no_price' => intval($child->no_price));
    $categoriesJs[] = "[". $child->category_id .",'". $child->name ."',false,false,2]";
  }
}
$categoriesJs = implode(",", $categoriesJs);
$categoriesJson = json_encode($categoriesJson);

$citiesList =
  (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City'))
    ? array(Yii::app()->user->model->profile->city->name => Yii::app()->user->model->profile->city_id)
    : City::getDataArray();
$citiesJs = array();

foreach ($citiesList as $city_name => $city_id) {
  $citiesJs[] = "[". $city_id .",'". $city_name ."']";
}
$citiesJs = implode(",", $citiesJs);

$paramsJs = json_encode($params);

$this->pageTitle = 'Редактировать объявление';

$this->renderPartial('_form', array('post' => $post));

$photo = ($post->photo) ?: "''";
$document = ($post->document) ?: "''";

$this->pageJS = <<<HTML
Advert.initForm({
  categoriesJson: $categoriesJson,
  categories: [$categoriesJs],
  cities: [$citiesJs],
  params: $paramsJs,
  attaches: {
    photo: $photo,
    document: $document
  }
});

cur.uploadAction = 'http://cs1.e-bash.me/upload.php';
HTML;
