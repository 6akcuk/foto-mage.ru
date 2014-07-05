<?php
/** @var DiscussForum $forum
 * @var DiscussForum $subforum
 */
?>
<?php foreach ($forums as $forum): ?>
<div class="discuss_forum">
  <div class="discuss_forum_header"><?php echo $forum->title ?></div>
  <?php foreach ($forum->subforums as $subforum): ?>
  <a href="/discuss<?php echo $subforum->forum_id ?>" onclick="return nav.go(this, event)" class="discuss_subforum clearfix">
    <div class="left discuss_forum_icon">
      <?php echo ActiveHtml::showUploadImage($subforum->icon, 'c') ?>
    </div>
    <div class="left discuss_forum_info">
      <?php echo $subforum->title ?>
      <div class="discuss_forum_description">
        <?php echo $subforum->description ?>
      </div>
    </div>
    <div class="right discuss_forum_stats">
      <div class="left discuss_forum_stat">
        <?php echo Yii::t('app', '<b>{n}</b><br>тема|<b>{n}</b><br>темы|<b>{n}</b><br>тем', $subforum->themesNum) ?>
      </div>
      <div class="left discuss_forum_stat">
        <?php echo Yii::t('app', '<b>{n}</b><br>сообщение|<b>{n}</b><br>сообщения|<b>{n}</b><br>сообщений', $subforum->postsNum) ?>
      </div>
    </div>
  </a>
  <?php endforeach; ?>
</div>
<?php endforeach; ?>