<?php
/** @var DiscussPost $attaches */
$attaches = json_decode($post->attaches, true);
?>
<div class="discuss_post_edit_wrap">
  <?php echo ActiveHtml::smartTextarea('discuss_post_editing', $post->post, array('onkeypress' => 'onCtrlEnter(event, function() { Discuss.doEditPost() })', 'style' => 'overflow: hidden; resize: none; height: 42px')) ?>
  <div id="dce_attaches" class="discuss_attaches clearfix">
  <?php foreach ($attaches as $attach): ?>
    <?php $photo = json_decode($attach, true); ?>
    <div class="left post_attach_photo">
      <input type="hidden" name="DiscussPost[attach][]" value='<?php echo $attach ?>' />
      <img src="http://cs<?php echo $photo['b'][2] ?>.<?php echo Yii::app()->params['domain'] ?>/<?php echo $photo['b'][0] ?>/<?php echo $photo['b'][1] ?>" alt=""/>
      <a class="tt photo_attach_delete" title="Удалить фотографию" onclick="Comment.onEditDelete(this)"><span class="icon-remove icon-white"></span></a>
    </div>
  <?php endforeach; ?>
  </div>
  <div class="discuss_buttons clearfix">
    <a class="button left" onclick="Discuss.doEditPost()">Сохранить</a>
    <a class="left button_cancel" onclick="Discuss.cancelEdit()">Отмена</a>
    <div id="dce_progress" class="upload left"><img src="/images/upload.gif" /></div>
    <div class="right">
      <?php echo ActiveHtml::upload('photo', '', 'Прикрепить фотографию', array('onchange' => 'Discuss.attachPhoto(\'#dce_attaches\', {id})')) ?>
    </div>
  </div>
</div>