<?php /** @var $comment Comment */ ?>
<?php foreach ($comments as $comment): ?>
  <div id="comment<?php echo $comment->comment_id ?>" class="reply" onmouseover="Wall.postOver('post<?php echo $comment->comment_id ?>', event)" onmouseout="Wall.postOut('post<?php echo $comment->comment_id ?>')" onclick="Comment.feedReplyTo(event, <?php echo $comment->comment_id ?>)">
    <div class="reply_table">
      <div class="reply_image">
        <?php echo ActiveHtml::link($comment->author->profile->getProfileImage('c'), '/id'. $comment->author_id, array('class' => 'reply_image')) ?>
      </div>
      <div class="reply_info">
        <?php if (Yii::app()->user->checkAccess('comment.deleteSuper') ||
          Yii::app()->user->checkAccess('comment.deleteOwn', array('comment' => $comment)) ||
          Yii::app()->user->checkAccess('comment.deleteOwner', array('hoop' => $hoop))): ?>
          <div class="right delete_reply_wrap">
            <div class="delete_post">
              <div title="Удалить" id="delete_post<?php echo $comment->comment_id ?>" onmouseover="Wall.postDeleteOver('post<?php echo $comment->comment_id ?>')" onmouseout="Wall.postDeleteOut('post<?php echo $comment->comment_id ?>')" onclick="event.cancelBubble = true; Comment.deleteFeed(<?php echo $comment->comment_id ?>);" class="icon-remove" style="opacity:0"></div>
            </div>
          </div>
        <?php endif; ?>
        <div class="reply_text">
          <?php echo ActiveHtml::link($comment->author->getDisplayName(), '/id'. $comment->author_id, array('class' => 'author', 'data-id' => $comment->author_id, 'data-name' => $comment->author->profile->firstname, 'data-lex-name' => ActiveHtml::lex(3, $comment->author->profile->firstname))) ?>
          <div id="wpt<?php echo $comment->comment_id ?>">
            <div class="wall_reply_text"><?php echo nl2br($comment->text) ?></div>
            <?php if ($comment->attaches != '[]'): ?>
              <div class="wall_attaches clearfix">
                <?php
                $attaches = json_decode($comment->attaches, true);

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
                  <a style="width: <?php echo $size[1] ?>px; height: <?php echo $size[2] ?>px" class="left wall_attached_photo" onclick="Photoview.show('comment<?php echo $comment->comment_id ?>', <?php echo $akey ?>)">
                    <img style="margin-left: <?php echo -(($use[3] - $size[1]) / 2) ?>px; margin-top: <?php echo -(($use[4] - $size[2]) / 2) ?>px" src="http://cs<?php echo $use[2] ?>.<?php echo Yii::app()->params['domain'] ?>/<?php echo $use[0] ?>/<?php echo $use[1] ?>" />
                  </a>
                <?php endforeach; ?>
                  <script>
                    Photoview.list('comment<?php echo $comment->comment_id ?>', <?php echo json_encode($list) ?>);
                  </script>
                <?php
                }
                ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="info_footer">
          <span class="rel_date"><?php echo ActiveHtml::timeback($comment->creation_date) ?></span>
          <?php if ($comment->reply_to): ?>
            <?php echo ActiveHtml::link(ActiveHtml::lex(3, $comment->reply->firstname), '/id'. $comment->reply_to, array('class' => 'comment_reply_to')) ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>
              