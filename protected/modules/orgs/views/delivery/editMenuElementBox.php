<?php
/** @var DeliveryCategory $category
 * @var ActiveForm $form
 */

Yii::app()->getClientScript()->registerCssFile('/css/delivery.css');
Yii::app()->getClientScript()->registerScriptFile('/js/delivery.js');

Yii::app()->getClientScript()->registerCssFile('/css/upload.css');
Yii::app()->getClientScript()->registerScriptFile('/js/upload.js');

$categoriesJs = array();

foreach ($categories as $category) {
  $categoriesJs[] = "[". $category->category_id .",'". $category->name ."']";
}
$categoriesJs = implode(",", $categoriesJs);

$this->pageTitle = 'Редактировать элемент меню доставки';

$this->renderPartial('_menuelementform', array('element' => $element));

$this->pageJS = <<<HTML
cur.uploadAction = 'http://cs1.e-bash.me/upload.php';

Delivery.initMenuElementForm({
  categories: [$categoriesJs]
});
HTML;
