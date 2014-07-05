<?php

Yii::app()->getClientScript()->registerCssFile('/css/discuss.css');
Yii::app()->getClientScript()->registerScriptFile('/js/discuss.js');

Yii::app()->getClientScript()->registerCssFile('/css/pagination.css');
Yii::app()->getClientScript()->registerScriptFile('/js/pagination.js');

$this->pageTitle = Yii::app()->name .' - Управление обсуждениями';
?>
<div class="tabs">
  <?php echo ActiveHtml::link('Обсуждения', '/discuss?act=manage', array('class' => 'selected')) ?>
  <div class="right">
    <?php echo ActiveHtml::link('Создать форум', '/discuss?act=create') ?>
  </div>
</div>
<div class="discuss_forums">
  <?php $this->renderPartial('_manageforums', array('forums' => $forums)) ?>
</div>