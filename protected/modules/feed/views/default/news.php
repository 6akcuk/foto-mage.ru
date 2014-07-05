<?php
/** @var $people User */

Yii::app()->getClientScript()->registerCssFile('/css/profile.css');

Yii::app()->getClientScript()->registerCssFile('/css/pagination.css');
Yii::app()->getClientScript()->registerScriptFile('/js/pagination.js');

Yii::app()->getClientScript()->registerCssFile('/css/feed.css');
Yii::app()->getClientScript()->registerCssFile('/css/wall.css');

$this->pageTitle = Yii::app()->name .' - Новости';
$delta = Yii::app()->controller->module->newsPerPage;
?>
<div class="tabs">
  <?php echo ActiveHtml::link('Новости', '/feed', array('class' => 'selected')) ?>
  <?php echo ActiveHtml::link('Ответы'. (($this->pageCounters['news'] > 0) ? ' <b>+'. $this->pageCounters['news'] .'</b>' : ''), '/feed?section=notifications') ?>
  <?php echo ActiveHtml::link('Комментарии', '/feed?section=comments') ?>
</div>
<div style="display: none">
  <?php $this->widget('Paginator', array(
    'url' => '/feed',
    'offset' => 0,
    'offsets' => $feedsNum,
    'delta' => $delta,
    'nopages' => true,
  )); ?>
  <div id="reply_box" class="reply_box clearfix" onclick="event.cancelBubble=true;" style="display:none">
    <?php echo ActiveHtml::link(Yii::app()->user->model->profile->getProfileImage('c'), '/id'. Yii::app()->user->getId(), array('class' => 'reply_form_image')) ?>
    <div class="reply_form">
      <div class="reply_field_wrap clearfix">
        <input type="hidden" id="reply_to" name="reply_to" />
        <input type="hidden" id="reply_to_title" name="reply_to_title" />
        <?php echo ActiveHtml::smartTextarea('reply_text', '', array('placeholder' => 'Комментировать..')) ?>
      </div>
      <div class="reply_attaches clearfix"></div>
      <div class="submit_reply clear">
        <a class="button left" onclick="Wall.doReply()">Отправить</a>
        <div class="left reply_to_title"></div>
        <div class="right reply_attach_btn">
          <?php echo ActiveHtml::upload('photo', '', 'Прикрепить фото', array('onchange' => 'Wall.replyAttachPhoto({id})')) ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php if ($feedsNum == 0): ?><div id="feed_empty">Здесь Вы будете видеть новостную ленту своих друзей и своего города.</div><?php endif; ?>
<div id="feeds" class="wide_wall" rel="pagination">
  <?php $this->renderPartial('_news', array('feeds' => $feeds, 'offset' => 0)) ?>
</div>
<?php if ($feedsNum > $delta): ?><a id="pg_more" class="pg_more" onclick="Paginator.showMore()">Еще новости</a><?php endif; ?>