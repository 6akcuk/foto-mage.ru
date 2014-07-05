<?php
/**
 * Created by JetBrains PhpStorm.
 * User: denis
 * Date: 28.02.13
 * Time: 10:11
 * To change this template use File | Settings | File Templates.
 */
/**
 * @var Comment $comment
 */
Yii::app()->getClientScript()->registerCssFile('/css/comments.css');
Yii::app()->getClientScript()->registerScriptFile('/js/comments.js');

Yii::app()->getClientScript()->registerCssFile('/css/photoview.css');
Yii::app()->getClientScript()->registerScriptFile('/js/photoview.js');
Yii::app()->getClientScript()->registerScriptFile('/js/jquery.cookie.js', null, 'after jquery-');

?>
<?php if ($offsets > 10): ?>
<a class="comment_show_more" onclick="Comment.showMore(<?php echo $this->hoop_id ?>, '<?php echo $this->hoop_type ?>', <?php echo $comments[0]->comment_id ?>)">
  <div class="wrh_text" id="wrh_text<?php echo $this->hoop_type ?>_<?php echo $this->hoop_id ?>">Показать все <?php echo Yii::t('app', '{n} комментарий|{n} комментария|{n} комментариев', $offsets) ?></div>
  <div class="wrh_prg" id="wrh_prg<?php echo $this->hoop_type ?>_<?php echo $this->hoop_id ?>"><img src="/images/upload.gif" /></div>
</a>
<?php endif; ?>
<div id="hoop<?php echo $this->hoop_id ?>_comments" class="comments_list">
  <?php foreach ($comments as $comment): ?>
  <?php $this->controller->renderPartial('//comment/_comment', array('comment' => $comment, 'hoop' => $this->hoop, 'reply' => $reply)) ?>
  <?php endforeach; ?>
</div>
<div class="comment_reply">
  <form id="hoop<?php echo $this->hoop_id ?>_form" action="comment/add?hoop_id=<?php echo $this->hoop_id ?>&hoop_type=<?php echo $this->hoop_type ?>" method="post">
  <h6>Ваш комментарий</h6>
  <?php echo ActiveHtml::smartTextarea('Comment[text]', '', array('placeholder' => 'Комментировать..', 'onkeydown' => 'if (event.ctrlKey && event.keyCode == 13) Comment.add('. $this->hoop_id .')')) ?>
  <div id="hoop<?php echo $this->hoop_id ?>_attaches" class="comment_post_attaches clearfix">
  </div>
  <div class="comment_post clearfix">
    <input type="hidden" id="reply_to_title" name="reply_to_title" />
    <div class="left">
      <a class="button" onclick="Comment.add(<?php echo $this->hoop_id ?>)">Отправить</a>
    </div>
    <div class="left reply_to_title"></div>
    <div id="comment<?php echo $this->hoop_id ?>_progress" class="left comment_post_progress">
      <img src="/images/upload.gif" />
    </div>
    <div class="right">
      <?php echo ActiveHtml::upload('photo', '', 'Прикрепить фотографию', array('onchange' => 'Comment.attachPhoto('. $this->hoop_id .', {id})')) ?>
    </div>
  </div>
  </form>
  <script>
  $.extend(A, {
    commentEditing: false,
    commentPhotoAttaches: 0,
    commentHoop: {
      <?php echo $this->hoop_id ?>: {
        timer: null, type: '<?php echo $this->hoop_type ?>', last_id: <?php echo (isset($comment->comment_id)) ? $comment->comment_id : 0 ?>,
        counter: 0
      }
    }
  });

  A.commentHoop[<?php echo $this->hoop_id ?>][0] = setTimeout(function() {
    Comment.peer(<?php echo $this->hoop_id ?>);
  }, 5000);
  </script>
</div>