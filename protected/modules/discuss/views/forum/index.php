<?php

Yii::app()->getClientScript()->registerCssFile('/css/discuss.css');
Yii::app()->getClientScript()->registerScriptFile('/js/discuss.js');

Yii::app()->getClientScript()->registerCssFile('/css/pagination.css');
Yii::app()->getClientScript()->registerScriptFile('/js/pagination.js');

$this->pageTitle = Yii::app()->name .' - Обсуждения';
?>
<div class="tabs">
  <?php echo ActiveHtml::link('Обсуждения', '/discuss', array('class' => 'selected')) ?>
</div>
<div class="discuss_forums">
  <?php $this->renderPartial('_forums', array('forums' => $forums)) ?>
</div>