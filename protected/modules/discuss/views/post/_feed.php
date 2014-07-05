<?php
Yii::app()->getClientScript()->registerCssFile('/css/discuss.css');
Yii::app()->getClientScript()->registerScriptFile('/js/discuss.js');

/** @var array $post */
$nullpost = $post['nullpost'];
$posts = $post['posts'];
$offsets = $post['offsets'];

?>
<div id="discuss<?php echo $nullpost->forum_id ?>_<?php echo $nullpost->theme_id ?>" last_id="<?php echo $posts[sizeof($posts) - 1]->post_id ?>" class="wall_post clearfix">
  <div class="post_table">
    <div class="post_image">
      <?php echo ActiveHtml::link(ActiveHtml::showUploadImage($nullpost->theme->forum->icon, 'c'), '/discuss'. $nullpost->forum_id .'_'. $nullpost->theme_id, array('class' => 'post_image')) ?>
    </div>
    <div class="post_info">
      <div class="wall_text wall_lnk">
        <?php echo ActiveHtml::link('<span class="icon-comment"></span> Тема <span class="a">'. $nullpost->theme->title .'</span>', '/discuss'. $nullpost->forum_id .'_'. $nullpost->theme_id, array('class' => 'author')) ?>
      </div>
      <div class="replies">
        <div class="replies_wrap" id="replies_wrap<?php echo $nullpost->forum_id ?>_<?php echo $nullpost->theme_id ?>">
          <div id="discuss_replies<?php echo $nullpost->forum_id ?>_<?php echo $nullpost->theme_id ?>">
          <?php if ($offsets > 3): ?>
            <a class="wr_header" onclick="Discuss.showMore('<?php echo $nullpost->forum_id ?>_<?php echo $nullpost->theme_id ?>', <?php echo $posts[0]->post_id ?>)">
              <div class="wrh_text" id="dch_text<?php echo $nullpost->forum_id ?>_<?php echo $nullpost->theme_id ?>"><?php if ($offsets > 100): ?>Показать последние 100 комментариев из <?php echo $offsets ?><?php else: ?>Показать все <?php echo Yii::t('app', '{n} комментарий|{n} комментария|{n} комментариев', $offsets) ?><?php endif; ?></div>
              <div class="wrh_prg" id="dch_prg<?php echo $nullpost->forum_id ?>_<?php echo $nullpost->theme_id ?>"><img src="/images/upload.gif" /></div>
            </a>
          <?php endif; ?>
          <?php $this->renderPartial('application.modules.discuss.views.post._feedlikereplies', array('theme' => $nullpost->theme, 'posts' => $posts)) ?>
          </div>
          <?php if ($nullpost->theme->closed == 0): ?>
          <div class="reply_fakebox_wrap" id="reply_fakebox_discuss<?php echo $nullpost->forum_id ?>_<?php echo $nullpost->theme_id ?>" onclick="Discuss.showReplyEditor(event, '<?php echo $nullpost->forum_id ?>_<?php echo $nullpost->theme_id ?>')">
            <div class="reply_fakebox">Комментировать..</div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>