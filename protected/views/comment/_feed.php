<?php
Yii::app()->getClientScript()->registerCssFile('/css/wall.css');
Yii::app()->getClientScript()->registerScriptFile('/js/wall.js');

Yii::app()->getClientScript()->registerScriptFile('/js/comments.js');

/** @var array $post */
$comments = $post['comments'];
$offsets = $post['offsets'];
$hoop_type = $post['hoop_type'];
$hoop_id = $post['hoop_id'];

switch ($hoop_type) {
  case 'good':
    /** @var Good $good */
    $good = Good::model()->with('image', 'purchase')->findByPk($hoop_id);

    $ref_image = ($good->image) ? $good->image->image : '';
    $ref_url = '/good'. $good->purchase_id .'_'. $good->good_id;
    $ref_title = '<span class="icon-briefcase"></span> Товар <span class="a">'. $good->name .'</span>';
    $ref_text = $good->description;

    $hoop = $good->purchase;
    break;
  case 'purchase':
    /** @var Purchase $purchase */
    $purchase = Purchase::model()->findByPk($hoop_id);

    $ref_image = $purchase->image;
    $ref_url = '/purchase'. $purchase->purchase_id;
    $ref_title = '<span class="icon-shopping-cart"></span> Закупка <span class="a">'. $purchase->name .'</span>';
    $ref_text = $purchase->shortstory;

    $hoop = $purchase;
    break;
}

?>
<div id="comment<?php echo $hoop_type ?>_<?php echo $hoop_id ?>" onmouseover="Wall.postOver('<?php echo $hoop_type ?>_<?php echo $hoop_id ?>', event)" onmouseout="Wall.postOut('<?php echo $hoop_type ?>_<?php echo $hoop_id ?>')" class="wall_post clearfix">
  <div class="post_table">
    <div class="post_image">
      <?php echo ActiveHtml::link(ActiveHtml::showUploadImage($ref_image, 'c'), $ref_url, array('class' => 'post_image')) ?>
    </div>
    <div class="post_info">
      <div class="wall_text wall_lnk">
        <?php echo ActiveHtml::link($ref_title, $ref_url, array('class' => 'author')) ?>
      </div>
      <div class="wall_post_text">
        <?php
        if (mb_strlen($ref_text, 'utf-8') > 300) {
          echo mb_substr($ref_text, 0, 300, 'utf-8');
          ?>
          <br>
          <a class="wall_post_more" onclick="$(this).hide().prev().hide(); $(this).next().show()">Показать полностью..</a>
          <span style="display: none"><?php echo mb_substr($ref_text, 300, -1, 'utf-8') ?></span>
        <?php
        }
        else echo $ref_text
        ?>
      </div>
      <div class="replies">
        <div class="replies_wrap" id="replies_wrap<?php echo $hoop_type ?>_<?php echo $hoop_id ?>">
          <div id="replies<?php echo $hoop_type ?>_<?php echo $hoop_id ?>">
            <?php if ($offsets > 3): ?>
              <a class="wr_header" onclick="Comment.showMore(<?php echo $hoop_id ?>, '<?php echo $hoop_type ?>', <?php echo $comments[0]->comment_id ?>, true)">
                <div class="wrh_text" id="wrh_text<?php echo $hoop_type ?>_<?php echo $hoop_id ?>"><?php if ($offsets > 100): ?>Показать последние 100 комментариев из <?php echo $offsets ?><?php else: ?>Показать все <?php echo Yii::t('app', '{n} комментарий|{n} комментария|{n} комментариев', $offsets) ?><?php endif; ?></div>
                <div class="wrh_prg" id="wrh_prg<?php echo $hoop_type ?>_<?php echo $hoop_id ?>"><img src="/images/upload.gif" /></div>
              </a>
            <?php endif; ?>
            <?php echo $this->renderPartial('application.views.comment._feedlikereplies', array('comments' => $comments, 'hoop' => $hoop)) ?>
          </div>
          <div class="reply_fakebox_wrap" id="reply_fakebox<?php echo $hoop_type ?>_<?php echo $hoop_id ?>" onclick="Comment.showReplyEditor(event, '<?php echo $hoop_type ?>_<?php echo $hoop_id ?>')">
            <div class="reply_fakebox">Комментировать..</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
$.extend(A.commentHoopFeed, {
  '<?php echo $hoop_type ?>_<?php echo $hoop_id ?>': {
    last_id: <?php $com = array_reverse($comments); echo $com[0]->comment_id ?>,
    counter: 0
  }
});
</script>