<?php /** @var $reply ProfileWallPost */ ?>
<?php foreach ($replies as $reply): ?>
<div id="post<?php echo $reply->wall_id ?>_<?php echo $reply->post_id ?>" class="reply" onclick="Wall.replyClick(event, '<?php echo $reply->wall_id ?>_<?php echo $reply->reply_to ?>', <?php echo $reply->post_id ?>)" onmouseover="Wall.postOver('post<?php echo $reply->wall_id ?>_<?php echo $reply->post_id ?>', event)" onmouseout="Wall.postOut('post<?php echo $reply->wall_id ?>_<?php echo $reply->post_id ?>')">
  <div class="reply_table">
    <div class="reply_image">
      <?php echo ActiveHtml::link($reply->author->profile->getProfileImage('c'), '/id'. $reply->author_id, array('class' => 'reply_image')) ?>
    </div>
    <div class="reply_info">
      <?php if (Yii::app()->user->getId() == $reply->author_id || Yii::app()->user->getId() == $reply->wall_id): ?>
        <div class="right delete_reply_wrap">
          <div class="delete_post">
            <div title="Удалить" id="delete_post<?php echo $reply->wall_id ?>_<?php echo $reply->post_id ?>" onmouseover="Wall.postDeleteOver('post<?php echo $reply->wall_id ?>_<?php echo $reply->post_id ?>')" onmouseout="Wall.postDeleteOut('post<?php echo $reply->wall_id ?>_<?php echo $reply->post_id ?>')" onclick="Wall.deleteReply('<?php echo $reply->wall_id ?>_<?php echo $reply->post_id ?>')" class="icon-remove" style="opacity:0"></div>
          </div>
        </div>
      <?php endif; ?>
      <div class="reply_text">
        <?php echo ActiveHtml::link($reply->author->getDisplayName(), '/id'. $reply->author_id, array('class' => 'author', 'data-id' => $reply->author_id, 'data-name' => $reply->author->profile->firstname, 'data-lex-name' => ActiveHtml::lex(3, $reply->author->profile->firstname))) ?>
        <div id="wpt<?php echo $reply->wall_id ?>_<?php echo $reply->post_id ?>">
          <div class="wall_reply_text"><?php echo nl2br($reply->post) ?></div>
          <?php if ($reply->attaches != '[]'): ?>
            <div class="wall_attaches clearfix">
              <?php
              $attaches = json_decode($reply->attaches, true);

              if (isset($attaches['photo'])) {
                $length = sizeof($attaches['photo']);

                $photo_sizes = array();
                $list = array('items' => array(), 'count' => $length);

                if ($length == 1) {
                  $attaches['photo'][0] = json_decode($attaches['photo'][0], true);
                  $photo_sizes[0] = array('e', min($attaches['photo'][0]['e'][3], 130), min($attaches['photo'][0]['e'][4], 130));
                }
                elseif ($length == 2) {
                  $attaches['photo'][0] = json_decode($attaches['photo'][0], true);
                  $attaches['photo'][1] = json_decode($attaches['photo'][1], true);

                  $min_height = min($attaches['photo'][0]['e'][4], $attaches['photo'][1]['e'][4]);

                  $photo_sizes[0] = array('e', min($attaches['photo'][0]['w'][3], 130), $min_height);
                  $photo_sizes[1] = array('e', min($attaches['photo'][1]['w'][3], 130), $min_height);
                }
                elseif ($length == 3) {
                  $attaches['photo'][0] = json_decode($attaches['photo'][0], true);
                  $attaches['photo'][1] = json_decode($attaches['photo'][1], true);
                  $attaches['photo'][2] = json_decode($attaches['photo'][2], true);

                  $min_height = min($attaches['photo'][0]['e'][4], $attaches['photo'][1]['e'][4]);

                  $photo_sizes[0] = array('e', min($attaches['photo'][0]['w'][3], 130), $min_height);
                  $photo_sizes[1] = array('e', min($attaches['photo'][0]['w'][3], 130), $min_height);
                  $photo_sizes[2] = array('e', min($attaches['photo'][0]['w'][3], 130), $min_height);
                }
                ?>
                <?php foreach ($attaches['photo'] as $akey => $photo): ?>
                <?php $list['items'][] = $photo ?>
                <?php $size = $photo_sizes[$akey] ?>
                <?php $use = $photo[$size[0]] ?>
                <a style="width: <?php echo $size[1] ?>px; height: <?php echo $size[2] ?>px" class="left wall_attached_photo" onclick="Photoview.show('wall<?php echo $reply->wall_id ?>_<?php echo $reply->post_id ?>', <?php echo $akey ?>)">
                  <img style="margin-left: <?php echo -(($use[3] - $size[1]) / 2) ?>px; margin-top: <?php echo -(($use[4] - $size[2]) / 2) ?>px" src="http://cs<?php echo $use[2] ?>.<?php echo Yii::app()->params['domain'] ?>/<?php echo $use[0] ?>/<?php echo $use[1] ?>" />
                </a>
              <?php endforeach; ?>
                <script>
                  Photoview.list('wall<?php echo $reply->wall_id ?>_<?php echo $reply->post_id ?>', <?php echo json_encode($list) ?>);
                </script>
              <?php
              }
              ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="info_footer">
        <span class="rel_date"><?php echo ActiveHtml::timeback($reply->add_date) ?></span>
        <?php if ($reply->reply_to_id): ?><?php echo ActiveHtml::link(ActiveHtml::lex(3, $reply->replyTo->firstname), '/id'. $reply->reply_to_id) ?><?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php if ($_reply == $reply->post_id): ?>
<script type="text/javascript">
$('#post<?php echo $reply->wall_id ?>_<?php echo $reply->post_id ?>').focuser();
</script>
<?php endif; ?>
<?php endforeach; ?>
              