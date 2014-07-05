<?php
/** @var $people User */

Yii::app()->getClientScript()->registerCssFile('/css/profile.css');
Yii::app()->getClientScript()->registerCssFile('/css/search.css');

Yii::app()->getClientScript()->registerScriptFile('/js/profile.js');

$this->pageTitle = Yii::app()->name .' - Друзья';
$delta = Yii::app()->controller->module->friendsPerPage;
?>
<div class="tabs">
  <?php echo ActiveHtml::link('Все друзья', '/friends') ?>
  <?php echo ActiveHtml::link('Друзья онлайн', '/friends?section=online') ?>
  <?php echo ActiveHtml::link('Заявки в друзья'. (($this->pageCounters['friends'] > 0) ? ' <b>+'. $this->pageCounters['friends'] .'</b>' : ''), '/friends?section=requests', array('class' => 'selected')) ?>
</div>
<div class="friends_search_wrap clearfix">
  <div class="minitabs clearfix">
    <?php echo ActiveHtml::link('Все подписчики', '/friends?section=allRequests') ?>
    <?php echo ActiveHtml::link('Исходящие заявки', '/friends?section=outRequests') ?>
    <?php echo ActiveHtml::link('Текущие заявки', '/friends?section=requests', array('class' => 'selected')) ?>
  </div>
</div>
<div class="summary_wrap">
  <?php $this->renderPartial('//layouts/pages', array(
    'url' => '/friends?section=requests',
    'offset' => $offset,
    'offsets' => $offsets,
    'delta' => $delta,
  )) ?>
  <div class="fl_r progress"></div>
  <div class="summary"><?php echo Yii::t('user', '{n} человек подал|{n} человека подали|{n} человек подали', $offsets) ?> заявку на добавление Вас в свой список друзей</div>
</div>
<div rel="pagination" class="searchresults">
  <?php echo $this->renderPartial('_request', array('peoples' => $peoples, 'offset' => $offset, 'current' => true)) ?>
</div>