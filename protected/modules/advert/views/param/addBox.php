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

$this->pageTitle = 'Добавить новый параметр';

$this->renderPartial('_form', array('param' => $param));

$this->pageJS = <<<HTML
AdvertParam.initForm({
  categories: [$categoriesJs]
});
HTML;
