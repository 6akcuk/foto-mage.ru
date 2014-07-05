<?php

Yii::app()->getClientScript()->registerCssFile('/css/discuss.css');
Yii::app()->getClientScript()->registerScriptFile('/js/discuss.js');

$this->pageTitle = Yii::app()->name .' - Новая тема';
?>
<div class="tabs">
  <?php echo ActiveHtml::link('Обсуждения', '/discuss') ?>
  <?php echo ActiveHtml::link('Просмотр форума', '/discuss'. $forum->forum_id) ?>
  <?php echo ActiveHtml::link('Новая тема', '/discuss'. $forum->forum_id .'?act=create', array('class' => 'selected')) ?>
</div>
<div class="bnt_wrap">
  <div class="bnt_header">Заголовок</div>
  <input type="text" id="bnt_title" />
  <div class="bnt_header">Текст</div>
  <textarea id="bnt_post" style="height: 250px"></textarea>
  <div id="bnt_attaches" class="bnt_attaches clearfix"></div>
  <div class="bnt_buttons clearfix">
    <a class="button left" onclick="Discuss.createTheme(<?php echo $forum->forum_id ?>)">Создать тему</a>
    <div id="bnt_progress" class="upload left"><img src="/images/upload.gif" /></div>
    <div class="right">
      <?php echo ActiveHtml::upload('photo', '', 'Прикрепить фотографию', array('onchange' => 'Discuss.attachPhoto({id})')) ?>
    </div>
  </div>
</div>
<script type="text/javascript">
A.discussPostPhotoAttaches = 0;
</script>