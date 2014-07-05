<?php
/**
 * @var DiscussPost $post
 */
$delta = Yii::app()->getModule('discuss')->postsPerPage;
$page = ($offset + $delta) / $delta;
$added = false;
?>
<?php foreach ($posts as $post): ?>
<?php $attaches = json_decode($post->attaches, true); ?>
<?php $length = sizeof($attaches); ?>
<div<?php if(!$added) { echo ' rel="page-'. $page .'"'; $added = true; } ?> id="discuss_post<?php echo $post->post_id ?>" class="discuss_post">
  <table>
    <tr>
      <td class="discuss_post_thumb_td">
        <?php echo ActiveHtml::link($post->author->profile->getProfileImage('c'), '/id'. $post->author_id, array('class' => 'discuss_post_thumb')) ?>
      </td>
      <td class="discuss_post_info">
        <div class="discuss_post_author_wrap">
          <?php echo ActiveHtml::link($post->author->getDisplayName(), '/id'. $post->author_id, array('class' => 'discuss_post_author', 'data-name' => $post->author->profile->firstname)) ?>
        </div>
        <div id="discuss_post_data<?php echo $post->post_id ?>">
          <div class="discuss_post_text">
          <?php
            $post->post = preg_replace("/\[post(\d+)\|(.*)\]/ui", "$2", $post->post);
          ?>
            <?php echo nl2br($post->post) ?>
          </div>
          <?php if($length): ?>
          <div class="discuss_post_attaches clearfix">
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
              <a style="width: <?php echo $size[1] ?>px; height: <?php echo $size[2] ?>px" class="left discuss_post_attached_photo" onclick="Photoview.show('discuss_post<?php echo $post->post_id ?>', <?php echo $akey ?>)">
                <img style="margin-left: <?php echo -(($use[3] - $size[1]) / 2) ?>px; margin-top: <?php echo -(($use[4] - $size[2]) / 2) ?>px" src="http://cs<?php echo $use[2] ?>.<?php echo Yii::app()->params['domain'] ?>/<?php echo $use[0] ?>/<?php echo $use[1] ?>" />
              </a>
            <?php endforeach; ?>
            <script>
              Photoview.list('discuss_post<?php echo $post->post_id ?>', <?php echo json_encode($list) ?>);
            </script>
          </div>
          <?php endif; ?>
        </div>
        <div class="discuss_post_bottom clearfix">
          <div class="left">
            <?php echo ActiveHtml::link(ActiveHtml::date($post->add_date), '/discuss'. $post->forum_id .'_'. $post->theme_id .'?post='. $post->post_id, array('class' => 'discuss_post_date')) ?>
            <?php if (Yii::app()->user->checkAccess('discuss.post.edit') &&
              Yii::app()->user->checkAccess('discuss.post.editOwn', array('post' => $post))): ?>
            <span class="divide">|</span>
            <a onclick="Discuss.editPost(<?php echo $post->post_id ?>)">Редактировать</a>
            <?php endif; ?>
            <?php if (Yii::app()->user->checkAccess('discuss.post.delete') &&
              (Yii::app()->user->checkAccess('discuss.post.deleteOwn', array('post' => $post))) ||
              Yii::app()->user->checkAccess('discuss.post.deleteSuper')): ?>
              <span class="divide">|</span>
              <a onclick="Discuss.deletePost(<?php echo $post->post_id ?>)">Удалить</a>
            <?php endif; ?>
            <?php if ($theme->closed == 0): ?>
            <span class="divide">|</span>
            <a onclick="Discuss.replyPost(<?php echo $post->post_id ?>)">Ответить</a>
            <?php endif; ?>
          </div>
          <div class="left progress dc_progress"></div>
        </div>
      </td>
    </tr>
  </table>
</div>
<?php if ($_post == $post->post_id): ?>
<script type="text/javascript">
$('#discuss_post<?php echo $post->post_id ?>').focuser();
</script>
<?php endif; ?>
<?php endforeach; ?>