<?php
/**
 * @var AdvertParam $param
 * @var AdvertCategory $category
 * @var ActiveForm $form
 */

Yii::app()->getClientScript()->registerCssFile('/css/advert.css');
Yii::app()->getClientScript()->registerScriptFile('/js/advert_params.js');

$categoriesJs = array();
foreach ($categories as $category) {
  $categoriesJs[] = "[". $category->category_id .",'". $category->name ."',true,true]";
  foreach ($category->childs as $child) {
    $categoriesJs[] = "[". $child->category_id .",'". $child->name ."',false,false,2]";
  }
}
$categoriesJs = implode(",", $categoriesJs);

$paramsJs = array();
foreach ($params as $prm) {
  $paramsJs[] = "[". $prm->param_id .",'". $prm->title ."',true,false]";
  foreach ($prm->childs as $child) {
    $paramsJs[] = "[". $child->param_id .",'". $child->title ."',false,false,2]";
    foreach ($child->childs as $wow) {
      $paramsJs[] = "[". $wow->param_id .",'". $child->title .' '. $wow->title ."',true,false,3]";
    }
  }
}
$paramsJs = implode(",", $paramsJs);

$this->pageTitle = 'Редактировать параметр';

$this->renderPartial('_form', array('param' => $param));

$this->pageJS = <<<HTML
AdvertParam.initForm({
  categories: [$categoriesJs],
  params: [$paramsJs]
});
HTML;
