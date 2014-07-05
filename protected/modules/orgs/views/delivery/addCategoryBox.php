<?php
/** @var DeliveryCategory $category
 * @var ActiveForm $form
 */

Yii::app()->getClientScript()->registerCssFile('/css/delivery.css');
Yii::app()->getClientScript()->registerScriptFile('/js/delivery_categories.js');

$this->pageTitle = 'Добавить категорию доставки';

$this->renderPartial('_categoryform', array('category' => $category));

$this->pageJS = <<<HTML
HTML;
