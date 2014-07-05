<?php

Yii::app()->getClientScript()->registerCssFile('/css/discuss.css');
Yii::app()->getClientScript()->registerScriptFile('/js/discuss.js');

Yii::app()->getClientScript()->registerCssFile('/css/pagination.css');
Yii::app()->getClientScript()->registerScriptFile('/js/pagination.js');

$this->pageTitle = Yii::app()->name .' - '. $forum->title;
$delta = Yii::app()->getModule('discuss')->themesPerPage;
?>
<div class="tabs">
  <?php echo ActiveHtml::link('Обсуждения', '/discuss') ?>
  <?php echo ActiveHtml::link('Просмотр форума', '/discuss'. $forum->forum_id, array('class' => 'selected')) ?>
  <div class="right">
    <?php echo ActiveHtml::link('Создать тему', '/discuss'. $forum->forum_id .'?act=create') ?>
  </div>
</div>
<div class="summary_wrap">
  <div class="right">
    <?php $this->widget('Paginator', array(
      'url' => '/discuss'. $forum->forum_id,
      'offset' => $offset,
      'offsets' => $themesNum,
      'delta' => $delta,
      'nopages' => true,
    )); ?>
  </div>
  <div class="summary"><?php echo $forum->title ?> - <?php echo Yii::t('app', '{n} тема|{n} темы|{n} тем', $themesNum) ?></div>
</div>
<div id="themes" rel="pagination">
  <?php $this->renderPartial('_themes', array('themes' => $themes, 'offset' => $offset)) ?>
</div>
<? if ($offset + $delta < $themesNum && $themesNum > $delta): ?><a id="pg_more" class="pg_more" onclick="Paginator.showMore()">Еще темы</a><? endif; ?>