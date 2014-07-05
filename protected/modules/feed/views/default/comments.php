<?php
/** @var $people User */

Yii::app()->getClientScript()->registerCssFile('/css/profile.css');

Yii::app()->getClientScript()->registerCssFile('/css/pagination.css');
Yii::app()->getClientScript()->registerScriptFile('/js/pagination.js');

Yii::app()->getClientScript()->registerCssFile('/css/feed.css');
Yii::app()->getClientScript()->registerCssFile('/css/wall.css');

$this->pageTitle = Yii::app()->name .' - Комментарии';
$delta = Yii::app()->controller->module->newsPerPage;
?>
<script type="text/javascript">
  A.commentHoopFeed = {};
  A.commentReplyOpened = false;
  A.discussPostReplyOpened = false;
</script>
  <div class="tabs">
    <?php echo ActiveHtml::link('Новости', '/feed') ?>
    <?php echo ActiveHtml::link('Ответы'. (($this->pageCounters['news'] > 0) ? ' <b>+'. $this->pageCounters['news'] .'</b>' : ''), '/feed?section=notifications') ?>
    <?php echo ActiveHtml::link('Комментарии', '/feed?section=comments', array('class' => 'selected')) ?>
  </div>
  <div rel="reply_parking_lot" style="display: none">
    <?php $this->widget('Paginator', array(
      'url' => '/feed',
      'offset' => 0,
      'offsets' => $feedsNum,
      'delta' => $delta,
      'nopages' => true,
    )); ?>
    <div id="reply_box" class="reply_box clearfix" onclick="event.cancelBubble=true;" style="display:none">
      <?php echo ActiveHtml::link(Yii::app()->user->model->profile->getProfileImage('c'), '/id'. Yii::app()->user->getId(), array('class' => 'reply_form_image')) ?>
      <div class="reply_form">
        <div class="reply_field_wrap clearfix">
          <input type="hidden" id="reply_to" name="reply_to" />
          <input type="hidden" id="reply_to_title" name="reply_to_title" />
          <?php echo ActiveHtml::smartTextarea('reply_text', '', array('placeholder' => 'Комментировать..', 'onkeypress' => 'onCtrlEnter(event, Wall.doReply)')) ?>
        </div>
        <div class="reply_attaches clearfix"></div>
        <div class="submit_reply clear">
          <a class="button left" onclick="Wall.doReply()">Отправить</a>
          <div class="left reply_to_title"></div>
          <div class="right reply_attach_btn">
            <?php echo ActiveHtml::upload('photo', '', 'Прикрепить фото', array('onchange' => 'Wall.replyAttachPhoto({id})')) ?>
          </div>
        </div>
      </div>
    </div>
    <div id="comment_box" class="reply_box clearfix" onclick="event.cancelBubble=true;" style="display:none">
      <?php echo ActiveHtml::link(Yii::app()->user->model->profile->getProfileImage('c'), '/id'. Yii::app()->user->getId(), array('class' => 'reply_form_image')) ?>
      <div class="reply_form">
        <div class="reply_field_wrap clearfix">
          <input type="hidden" id="com_reply_to_title" name="reply_to_title" />
          <?php echo ActiveHtml::smartTextarea('Comment[text]', '', array('placeholder' => 'Комментировать..', 'onkeypress' => 'onCtrlEnter(event, Comment.doReply)')) ?>
        </div>
        <div class="reply_attaches clearfix"></div>
        <div class="submit_reply clear">
          <a class="button left" onclick="Comment.doReply()">Отправить</a>
          <div class="left reply_to_title"></div>
          <div class="right reply_attach_btn">
            <?php echo ActiveHtml::upload('photo', '', 'Прикрепить фото', array('onchange' => 'Comment.replyAttachPhoto({id})')) ?>
          </div>
        </div>
      </div>
    </div>
    <div id="discuss_post_box" class="reply_box clearfix" onclick="event.cancelBubble=true;" style="display:none">
      <?php echo ActiveHtml::link(Yii::app()->user->model->profile->getProfileImage('c'), '/id'. Yii::app()->user->getId(), array('class' => 'reply_form_image')) ?>
      <div class="reply_form">
        <div class="reply_field_wrap clearfix">
          <?php echo ActiveHtml::smartTextarea('dcp_text', '', array('placeholder' => 'Комментировать..', 'onkeypress' => 'onCtrlEnter(event, Discuss.doReply)')) ?>
        </div>
        <div id="dcp_attaches" class="reply_attaches clearfix"></div>
        <div class="submit_reply clear">
          <a class="button left" onclick="Discuss.doReply()">Отправить</a>
          <div id="bnt_progress" class="upload left"><img src="/images/upload.gif" /></div>
          <div class="right reply_attach_btn">
            <?php echo ActiveHtml::upload('photo', '', 'Прикрепить фото', array('onchange' => 'Discuss.attachPhoto(\'#dcp_attaches\', {id})')) ?>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php if ($feedsNum == 0): ?><div id="feed_empty">Здесь Вы будете видеть новостную ленту своих друзей и своего города.</div><?php endif; ?>
  <div id="feeds" class="wide_wall" rel="pagination">
    <?php $this->renderPartial('_comments', array('feeds' => $feeds, 'offset' => 0)) ?>
  </div>
<?php if ($feedsNum > $delta): ?><a id="pg_more" class="pg_more" onclick="Paginator.showMore()">Еще новости</a><?php endif; ?>