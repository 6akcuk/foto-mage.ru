<?php
/** @var DiscountAction $action
 * @var ActiveForm $form
 */

Yii::app()->getClientScript()->registerCssFile('/css/orgs.css');
Yii::app()->getClientScript()->registerScriptFile('/js/discount.js');

Yii::app()->getClientScript()->registerCssFile('/css/upload.css');
Yii::app()->getClientScript()->registerScriptFile('/js/upload.js');

$this->pageTitle = 'Выдать дисконтную карту';

$this->renderPartial('_cardform', array('action' => $action));

$this->pageJS = <<<HTML
cur.uploadAction = 'http://cs1.e-bash.me/upload.php';
Discount.initCardForm();
HTML;
