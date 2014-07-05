<?php
/** @var DeliveryGood $good
 * @var DeliveryMenuElement $item
 * @var ActiveForm $form
 */

Yii::app()->getClientScript()->registerCssFile('/css/delivery.css');
Yii::app()->getClientScript()->registerScriptFile('/js/delivery.js');

Yii::app()->getClientScript()->registerCssFile('/css/upload.css');
Yii::app()->getClientScript()->registerScriptFile('/js/upload.js');

$categoriesJs = array();
foreach ($categories as $data) {
  $categoriesJs[] = "[". $data['model']->category->category_id .",'". $data['model']->category->name ."',true,true]";
  foreach ($data['items'] as $item) {
    $categoriesJs[] = "[". $item->element_id .",'". $item->name ."',false,false,2]";
  }
}
$categoriesJs = implode(",", $categoriesJs);

$this->pageTitle = 'Добавить товар в меню доставки';

$this->renderPartial('_goodform', array('good' => $good));

$this->pageJS = <<<HTML
cur.uploadAction = 'http://cs1.e-bash.me/upload.php';

Delivery.initGoodForm({
  categories: [$categoriesJs]
});
HTML;
