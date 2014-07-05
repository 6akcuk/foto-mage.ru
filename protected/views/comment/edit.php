<?php
/** @var $comment Comment */
$attaches = json_decode($comment->attaches, true);
?>
<form id="comment<?php echo $comment->comment_id ?>_form" action="comment/edit/id/<?php echo $comment->comment_id ?>" method="post">
  <?php echo ActiveHtml::smartTextarea('Comment[text]', $comment->text, array('placeholder' => 'Комментировать..')) ?>
  <div id="comment<?php echo $comment->comment_id ?>_attaches" class="comment_post_attaches clearfix">
  <?php foreach ($attaches as $attach): ?>
  <?php $photo = json_decode($attach, true); ?>
    <div class="left comment_attach_photo">
      <input type="hidden" name="Comment[attach][]" value='<?php echo $attach ?>' />
      <img src="http://cs<?php echo $photo['b'][2] ?>.<?php echo Yii::app()->params['domain'] ?>/<?php echo $photo['b'][0] ?>/<?php echo $photo['b'][1] ?>" alt=""/>
      <a class="tt photo_attach_delete" title="Удалить фотографию" onclick="Comment.onEditDelete(this)"><span class="icon-remove icon-white"></span></a>
    </div>
  <?php endforeach; ?>
  </div>
  <div class="comment_post clearfix">
    <div class="left">
      <a class="button" onclick="Comment.doEdit(<?php echo $comment->comment_id ?>)">Сохранить</a>
      <a onclick="Comment.cancelEdit(<?php echo $comment->comment_id ?>)" style="margin-left: 10px">Отменить</a>
    </div>
    <div id="comment<?php echo $comment->comment_id ?>_editprogress" class="left comment_post_progress">
      <img src="/images/upload.gif" />
    </div>
  </div>
</form>