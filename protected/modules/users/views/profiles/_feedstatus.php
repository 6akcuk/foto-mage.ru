<?php
/** @var Feed $feed */
?>
<div id="feed_status<?php echo $feed->feed_id ?>_<?php echo $feed->owner_id ?>" class="wall_post clearfix">
  <div class="post_table">
    <div class="post_image">
      <?php echo ActiveHtml::link($feed->content->profile->getProfileImage('c'), '/id'. $feed->event_link_id, array('class' => 'post_image')) ?>
    </div>
    <div class="post_info">
      <div class="wall_text">
        <?php echo ActiveHtml::link($feed->content->getDisplayName(), '/id'. $feed->event_link_id, array('class' => 'author')) ?>
        <span class="desc">обновил<?php if ($feed->content->profile->gender == 'Female') echo 'а' ?> статус на своей страничке</span>
        <div id="wpt_status<?php echo $feed->feed_id ?>_<?php echo $feed->owner_id ?>">
          <div class="wall_post_text">
            <?php echo $feed->event_text ?>
          </div>
        </div>
      </div>
      <div class="replies">
        <div class="reply_link_wrap">
          <small>
            <span class="rel_date"><?php echo ActiveHtml::timeback($feed->add_date) ?></span>
          </small>
        </div>
      </div>
    </div>
  </div>
</div>