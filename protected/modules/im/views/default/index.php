<?php
/** @var $people User */

Yii::app()->getClientScript()->registerCssFile('/css/profile.css');
Yii::app()->getClientScript()->registerCssFile('/css/im.css');

Yii::app()->getClientScript()->registerCssFile('/css/pagination.css');
Yii::app()->getClientScript()->registerScriptFile('/js/pagination.js');

Yii::app()->getClientScript()->registerScriptFile('/js/im.js');

$this->pageTitle = Yii::app()->name .' - Диалоги';
$delta = Yii::app()->controller->module->dialogsPerPage;
?>
<div class="tabs">
    <?php echo ActiveHtml::link('Диалоги', '/im', array('class' => 'selected')) ?>
</div>
<div class="im_bar clearfix">
    <div rel="filters" class="left">
        <?php echo ActiveHtml::inputPlaceholder('c[name]', (isset($c['name'])) ? $c['name'] : '', array('placeholder' => 'Поиск по диалогам')) ?>
    </div>
    <div class="right">
        <?php echo ActiveHtml::link('Написать сообщение', '/im?sel=-1', array('class' => 'button')) ?>
    </div>
    <div style="display: none">
        <?php $this->widget('Paginator', array(
        'url' => '/im',
        'offset' => $offset,
        'offsets' => $offsets,
        'delta' => $delta,
        'nopages' => true,
    )); ?>
    </div>
</div>
<div class="summary_wrap">
  <div class="summary">
    <?php echo Yii::t('user', '{n} диалог|{n} диалога|{n} диалогов', $offsets) ?>
    <span class="divide">|</span>
    <span><?php echo ActiveHtml::link('Показать в виде сообщений', array('/mail')) ?></span>
  </div>
</div>
<div id="im_dialogs" rel="pagination">
    <div class="im_none" id="im_rows_none" style="display:<?php echo ($offsets) ? 'none' : 'block' ?>">Здесь будет выводиться список Ваших сообщений</div>
    <?php $this->renderPartial('_dialog', array('dialogs' => $dialogs, 'offset' => $offset)) ?>
</div>
<? if ($offset + $delta < $offsets && $offsets > $delta): ?><a id="pg_more" class="pg_more" onclick="Paginator.showMore()">Еще диалоги</a><? endif; ?>