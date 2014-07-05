<?php
/** @var DeliveryGood $good
 * @var DeliveryMenuElement $item
 * @var ActiveForm $form
 */

Yii::app()->getClientScript()->registerCssFile('/css/market.css');
Yii::app()->getClientScript()->registerScriptFile('/js/market.js');

Yii::app()->getClientScript()->registerCssFile('/css/upload.css');
Yii::app()->getClientScript()->registerScriptFile('/js/upload.js');

$categoriesJs = array();
foreach ($categories as $category) {
  $categoriesJs[] = "[". $category->category_id .",'". $category->name ."',true,true]";
  foreach ($category->childs as $child) {
    $categoriesJs[] = "[". $child->category_id .",'". $child->name ."',false,false,2]";
  }
}
$categoriesJs = implode(",", $categoriesJs);

$this->pageTitle = 'Редактировать товар';

$this->renderPartial('_goodform', array('good' => $good));

$this->pageJS = <<<HTML
cur.uploadAction = 'http://cs1.e-bash.me/upload.php';

Market.initGoodForm({
  categories: [$categoriesJs]
});
HTML;
