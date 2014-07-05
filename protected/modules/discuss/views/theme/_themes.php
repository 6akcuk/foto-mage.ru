<?php
/**
 * @var DiscussTheme $theme
 */
$delta = Yii::app()->getModule('discuss')->postsPerPage;
?>
<?php foreach ($themes as $theme): ?>
<?php $pages = floor($theme->postsNum / $delta) ?>
<div class="discuss_theme clearfix">
  <a class="right discuss_theme_last" href="/discuss<?php echo $theme->forum_id ?>_<?php echo $theme->theme_id ?>?offset=last&scroll=1" onclick="return nav.go(this, event)">
    <div class="left discuss_theme_thumb">
      <?php echo ActiveHtml::showUploadImage($theme->lastPost->author->profile->photo, 'c', array('class' => 'discuss_theme_img')) ?>
    </div>
    <div class="left discuss_theme_mem"><?php echo $theme->lastPost->author->getDisplayName() ?></div>
    <div class="left discuss_theme_date">ответил<?php if ($theme->lastPost->author->profile->gender == 'Female') echo 'а' ?> <?php echo ActiveHtml::date($theme->lastPost->add_date) ?></div>
  </a>
  <div class="discuss_theme_info">
    <div>
      <?php echo ActiveHtml::link($theme->title, '/discuss'. $theme->forum_id .'_'. $theme->theme_id, array('class' => 'discuss_theme_title')) ?>
      <?php if ($theme->fixed || $theme->closed): ?>
      <nobr class="discuss_theme_closed"><?php if ($theme->fixed && $theme->closed) echo 'тема закреплена и закрыта'; elseif ($theme->fixed) echo 'тема закреплена'; else echo 'тема закрыта' ?></nobr>
      <?php endif; ?>
    </div>
    <div class="discuss_theme_other">
      <span class="discuss_theme_msgs">
        <?php echo Yii::t('app', '<b>{n}</b> сообщение|<b>{n}</b> сообщения|<b>{n}</b> сообщений', $theme->postsNum) ?>
      </span>
      <?php if ($pages): ?>
      <span class="discuss_theme_pages">
        Стр.
        <?php if ($pages <= 7): ?>
        <?php for($i = 1; $i <= $pages; $i++): ?>
          <?php echo ActiveHtml::link($i, '/discuss'. $theme->forum_id .'_'. $theme->theme_id .'?offset='. (($i * $delta) - $delta), array('class' => 'discuss_theme_page')) ?>
        <?php endfor; ?>
        <?php else: ?>
        <?php for($i = 1; $i <= 3; $i++): ?>
          <?php echo ActiveHtml::link($i, '/discuss'. $theme->forum_id .'_'. $theme->theme_id .'?offset='. (($i * $delta) - $delta), array('class' => 'discuss_theme_page')) ?>
        <?php endfor; ?>
          ..
        <?php for($i = ($pages - 2); $i <= $pages; $i++): ?>
          <?php echo ActiveHtml::link($i, '/discuss'. $theme->forum_id .'_'. $theme->theme_id .'?offset='. (($i * $delta) - $delta), array('class' => 'discuss_theme_page')) ?>
        <?php endfor; ?>
        <?php endif; ?>
      </span>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endforeach; ?>