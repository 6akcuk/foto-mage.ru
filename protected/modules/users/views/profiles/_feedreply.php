<?php
/** @var Feed $feed */
?>
<div class="feedback_answered_row_wrap" id="feedback_row_wrap_wall_reply<?php echo $feed->content->wall_id ?>_<?php echo $feed->content->post_id ?>">
  <div class="feedback_row_wrap" id="feedback_row_wall_reply<?php echo $feed->content->wall_id ?>_<?php echo $feed->content->post_id ?>">
    <div class="feedback_row">
      <table class="feedback_row_t">
        <tr>
          <td class="feedback_row_photo">
            <div class="feedback_row_photo" onclick="event.cancelBubble = true">
              <?php echo ActiveHtml::link($feed->content->author->profile->getProfileImage('c'), '/id'. $feed->content->author_id) ?>
            </div>
          </td>
          <td class="feedback_row_content">
            <div class="feedback_row_content">
              <?php echo ActiveHtml::link($feed->content->author->getDisplayName(), '/id'. $feed->content->author_id) ?>
              <div class="feedback_row_text">
                <div id="wpt<?php echo $feed->content->wall_id ?>_<?php echo $feed->content->post_id ?>">
                  <div class="wall_reply_text">
                    <?php echo nl2br($feed->content->post) ?>
                  </div>
                </div>
              </div>
              <div class="feedback_row_date">
                <?php echo ActiveHtml::link('<span class="rel_date">'. ActiveHtml::date($feed->content->add_date, true, true) .'</span>', '/wall'. $feed->content->wall_id .'_'. $feed->content->replyPost->post_id .'?reply='. $feed->content->post_id) ?>
                в комментариях к записи
                <?php
                if ($feed->content->replyPost->post):
                  echo ActiveHtml::link(mb_substr($feed->content->replyPost->post, 0, 40, 'utf-8') .'..', '/wall'. $feed->content->wall_id .'_'. $feed->content->replyPost->post_id);
                else:
                  echo 'от '. ActiveHtml::link(ActiveHtml::date($feed->content->replyPost->add_date, true, true), '/wall'. $feed->content->wall_id .'_'. $feed->content->replyPost->post_id);
                endif;
                ?>
              </div>
            </div>
          </td>
          <td class="feedback_row_photo">
            <div class="feedback_row_photo">

            </div>
          </td>
        </tr>
      </table>
    </div>
  </div>
</div>