<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 02.03.13
 * Time: 21:28
 * To change this template use File | Settings | File Templates.
 */

/** @var $comment Comment */
$attaches = json_decode($comment->attaches, true);
$length = sizeof($attaches);

if (!isset($reply)) $reply = null;

?>
<div id="comment_<?php echo $comment->comment_id ?>" class="comment_block clearfix" onclick="Comment.replyTo(event, <?php echo $comment->comment_id ?>)">
  <div class="left photo">
    <?php echo ActiveHtml::link($comment->author->profile->getProfileImage('c'), '/id'. $comment->author_id) ?>
  </div>
  <div class="left comment_data">
    <div class="comment_header">
      <?php echo ActiveHtml::link($comment->author->getDisplayName(), '/id'. $comment->author_id, array('class' => 'comment_author',  'data-id' => $comment->author_id, 'data-name' => trim($comment->author->profile->firstname), 'data-lex-name' => ActiveHtml::lex(3, trim($comment->author->profile->firstname)))) ?>
    </div>
    <div class="comment_text">
      <?php echo nl2br($comment->text) ?>
    </div>
    <?php if($length): ?>
    <div class="comment_attaches clearfix">
      <?php
      $photo_sizes = array();
      $list = array('items' => array(), 'count' => $length);

      if ($length == 1) {
        $attaches[0] = json_decode($attaches[0], true);
        $photo_sizes[0] = array('w', min($attaches[0]['w'][3], 604), min($attaches[0]['w'][4], 604));
      }
      elseif ($length == 2) {
        $attaches[0] = json_decode($attaches[0], true);
        $attaches[1] = json_decode($attaches[1], true);

        $min_height = min($attaches[0]['d'][4], $attaches[1]['d'][4]);

        $photo_sizes[0] = array('d', min($attaches[0]['w'][3], 320), $min_height);
        $photo_sizes[1] = array('d', min($attaches[1]['w'][3], 320), $min_height);
      }
      elseif ($length == 3) {
        $attaches[0] = json_decode($attaches[0], true);
        $attaches[1] = json_decode($attaches[1], true);
        $attaches[2] = json_decode($attaches[2], true);

        $master_height = floor(434 * $attaches[0]['w'][4]) / $attaches[0]['w'][3];
        $slave_height = $master_height / 2;

        $photo_sizes[0] = array('w', min($attaches[0]['w'][3], 434), $master_height);
        $photo_sizes[1] = array('d', min($attaches[1]['w'][3], 210), $slave_height);
        $photo_sizes[2] = array('d', min($attaches[2]['w'][3], 210), $slave_height);
      }
      ?>
      <?php foreach ($attaches as $akey => $photo): ?>
      <?php $list['items'][] = $photo ?>
      <?php $size = $photo_sizes[$akey] ?>
      <?php $use = $photo[$size[0]] ?>
      <a style="width: <?php echo $size[1] ?>px; height: <?php echo $size[2] ?>px" class="left comment_attached_photo" onclick="Photoview.show('comment<?php echo $comment->comment_id ?>', <?php echo $akey ?>)">
        <img style="margin-left: <?php echo -(($use[3] - $size[1]) / 2) ?>px; margin-top: <?php echo -(($use[4] - $size[2]) / 2) ?>px" src="http://cs<?php echo $use[2] ?>.<?php echo Yii::app()->params['domain'] ?>/<?php echo $use[0] ?>/<?php echo $use[1] ?>" />
      </a>
      <?php endforeach; ?>
      <script>
        Photoview.list('comment<?php echo $comment->comment_id ?>', <?php echo json_encode($list) ?>);
      </script>
    </div>
    <?php endif; ?>
    <div class="comment_control" onclick="event.cancelBubble = true;">
      <span class="comment_date"><?php echo ActiveHtml::timeback($comment->creation_date) ?></span>
      <?php if ($comment->reply_to): ?>
      <?php echo ActiveHtml::link(ActiveHtml::lex(3, $comment->reply->firstname), '/id'. $comment->reply_to, array('class' => 'comment_reply_to')) ?>
      <?php endif; ?>
      | <a onclick="Comment.replyTo(event, <?php echo $comment->comment_id ?>)">Ответить</a>
      <?php if (Yii::app()->user->checkAccess('comment.editSuper') ||
        Yii::app()->user->checkAccess('comment.editOwn', array('comment' => $comment))): ?>
      | <a id="comment_<?php echo $comment->comment_id ?>_edit" onclick="Comment.edit(<?php echo $comment->comment_id ?>)">Редактировать</a>
      <?php endif; ?>
      <?php if (Yii::app()->user->checkAccess('comment.deleteSuper') ||
        Yii::app()->user->checkAccess('comment.deleteOwn', array('comment' => $comment)) ||
        Yii::app()->user->checkAccess('comment.deleteOwner', array('hoop' => $hoop))): ?>
      | <a id="comment_<?php echo $comment->comment_id ?>_delete" onclick="Comment.delete(<?php echo $comment->comment_id ?>)">Удалить</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php if ($reply == $comment->comment_id): ?>
<script type="text/javascript">
$('#comment_<?php echo $comment->comment_id ?>').focuser();
</script>
<?php endif; ?>