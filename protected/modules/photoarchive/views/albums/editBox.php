<?php
/**
 * @var News $post
 * @var ActiveForm $form
 */

Yii::app()->getClientScript()->registerCssFile('/css/albums.css');
Yii::app()->getClientScript()->registerScriptFile('/js/albums.js');

$this->pageTitle = 'Редактировать альбом';

$this->renderPartial('_form', array('album' => $album));

$this->pageJS = <<<HTML
HTML;
