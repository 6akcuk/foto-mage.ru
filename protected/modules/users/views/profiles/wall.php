<?php
Yii::app()->getClientScript()->registerCssFile('/css/pagination.css');
Yii::app()->getClientScript()->registerScriptFile('/js/pagination.js');

Yii::app()->getClientScript()->registerCssFile('/css/wall.css');
Yii::app()->getClientScript()->registerScriptFile('/js/wall.js');

$this->pageTitle = Yii::app()->name .' - Стена';
$delta = Yii::app()->getModule('users')->wallPostsPerPage;
?>
<div class="tabs">
  <?php echo ActiveHtml::link('Все записи', '/wall'. $id, array('class' => ($post) ? '' : 'selected')) ?>
  <?php if ($post): ?>
  <?php echo ActiveHtml::link('Запись на стене', '/wall'. $id .'_'. $post->post_id, array('class' => 'selected')) ?>
  <?php endif; ?>
</div>
<div rel="reply_parking_lot" class="summary_wrap"<?php if ($post): ?> style="display:none"<?php endif; ?>>
  <?php if (!$post): ?>
  <div class="summary">Всего <?php echo Yii::t('app', '{n} запись|{n} записи|{n} записей', $offsets) ?></div>
  <div class="right">
    <?php $this->widget('Paginator', array(
      'url' => '/wall'. $id,
      'offset' => $offset,
      'offsets' => $offsets,
      'delta' => $delta,
    )); ?>
  </div>
  <?php endif; ?>

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
        <a class="button left" onclick="Wall.doReply(<?php echo $id ?>)">Отправить</a>
        <div class="left reply_to_title"></div>
        <div class="right reply_attach_btn">
          <?php echo ActiveHtml::upload('photo', '', 'Прикрепить фото', array('onchange' => 'Wall.replyAttachPhoto({id})')) ?>
        </div>
      </div>
    </div>
  </div>

  <script type="text/javascript">
    $.extend(A, {
      wallLastID: <?php echo (isset($posts[0])) ? $posts[0]->post_id : 0 ?>,
      wallPhotoAttaches: 0,
      wallReplyPhotoAttaches: 0
    });
  </script>
</div>

<div id="wall<?php echo $id ?>" class="wide_wall" rel="pagination">
<?php if (!$post): ?>
  <?php echo $this->renderPartial('_wall', array('posts' => $posts, 'offset' => 0)) ?>
<?php else: ?>
  <?php echo $this->renderPartial('_wallpost', array('post' => $post, 'reply' => $reply)) ?>
<?php endif; ?>
</div>
<? if (0 + Yii::app()->getModule('users')->wallPostsPerPage < $offsets && $offsets > Yii::app()->getModule('users')->wallPostsPerPage): ?><a id="pg_more" class="pg_more" onclick="Paginator.showMore()">Еще записи</a><? endif; ?>