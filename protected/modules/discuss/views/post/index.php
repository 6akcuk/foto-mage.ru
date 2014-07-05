<?php

Yii::app()->getClientScript()->registerCssFile('/css/discuss.css');
Yii::app()->getClientScript()->registerScriptFile('/js/discuss.js');

Yii::app()->getClientScript()->registerCssFile('/css/pagination.css');
Yii::app()->getClientScript()->registerScriptFile('/js/pagination.js');

Yii::app()->getClientScript()->registerCssFile('/css/photoview.css');
Yii::app()->getClientScript()->registerScriptFile('/js/photoview.js');

$this->pageTitle = Yii::app()->name .' - '. $theme->title;
$delta = Yii::app()->getModule('discuss')->postsPerPage;
?>
<div class="tabs">
  <?php echo ActiveHtml::link('Обсуждения', '/discuss') ?>
  <?php echo ActiveHtml::link('Просмотр темы', '/discuss'. $forum->forum_id .'_'. $theme->theme_id, array('class' => 'selected')) ?>
  <?php if (Yii::app()->user->checkAccess('discuss.theme.edit') ||
    Yii::app()->user->checkAccess('discuss.theme.editOwn', array('theme' => $theme))): ?>
  <div class="progress right" id="edit_theme_progress"></div>
  <a class="right" rel="menu">Редактировать</a>
  <div id="discuss_theme_menu" style="display: none">
    <a onclick="Discuss.editTheme(<?php echo $theme->theme_id ?>)">Изменить название</a>
    <?php if (Yii::app()->user->checkAccess('discuss.theme.fix')): ?>
    <a onclick="Discuss.<?php echo ($theme->fixed == 0) ? 'fix' : 'unfix' ?>Theme(<?php echo $theme->theme_id ?>)"><?php echo ($theme->fixed == 0) ? 'Закрепить' : 'Не закреплять' ?> тему</a>
    <?php endif; ?>
    <a onclick="Discuss.<?php echo ($theme->closed == 0) ? 'close' : 'open' ?>Theme(<?php echo $theme->theme_id ?>)"><?php echo ($theme->closed == 0) ? 'Закрыть' : 'Открыть' ?> тему</a>
    <a onclick="Discuss.deleteTheme(<?php echo $theme->theme_id ?>)">Удалить тему</a>
  </div>
  <?php endif; ?>
</div>
<div class="discuss_header">
  <?php echo ActiveHtml::link($theme->title, '/discuss'. $forum->forum_id .'_'. $theme->theme_id, array('id' => 'discuss_title')) ?>
  <div class="discuss_author">
    <?php echo ActiveHtml::link($theme->author->getDisplayName(), '/id'. $theme->author_id, array('class' => 'mem_link')) ?>
  </div>
</div>
<?php if (isset($_SESSION['discuss.message.title'])): ?>
<div id="dc_msg" class="msg">
  <b><?php echo $_SESSION['discuss.message.title'] ?></b><br/>
  <?php echo $_SESSION['discuss.message.body'] ?>
  <?php unset($_SESSION['discuss.message.title']); unset($_SESSION['discuss.message.body']); ?>
</div>
<?php endif; ?>
<div class="summary_wrap">
  <div class="right">
    <?php $this->widget('Paginator', array(
      'url' => '/discuss'. $forum->forum_id .'_'. $theme->theme_id,
      'offset' => $offset,
      'offsets' => $postsNum,
      'delta' => $delta,
    )); ?>
  </div>
  <div class="summary">В теме <?php echo Yii::t('app', '{n} сообщение|{n} сообщения|{n} сообщений', $postsNum) ?></div>
</div>
<div id="posts_rows" rel="pagination">
  <?php $this->renderPartial('_posts', array('_post' => $_post, 'theme' => $theme, 'posts' => $posts, 'offset' => $offset)) ?>
</div>
<? if ($offset + $delta < $postsNum && $postsNum > $delta): ?><a id="pg_more" class="pg_more" onclick="Paginator.showMore()">Еще сообщения</a><? endif; ?>
<script type="text/javascript">
  A.discussPostFixed = false;
  A.postEditing = false;
  A.postEditingProgress = false;
  A.postDeleting = false;
  <?php if($scroll == 1): ?>A.pageScroll = true; $(window).scrollTop(65500);<?php endif; ?>
</script>
<?php if ($theme->closed == 0): ?>
<div id="discuss_fixer"></div>
<div id="discuss_post_wrap">
  <div id="discuss_post">
    <div id="discuss_post_sh"></div>
    <table>
      <tr>
        <td class="discuss_post_thumb_td">
          <?php echo ActiveHtml::link(Yii::app()->user->model->profile->getProfileImage('c'), '/id'. Yii::app()->user->getId(), array('class' => 'discuss_post_thumb')) ?>
        </td>
        <td class="discuss_post_info">
          <?php echo ActiveHtml::smartTextarea('discuss_text', '', array('onkeypress' => 'onCtrlEnter(event, function() { Discuss.sendPost(\''. $forum->forum_id .'_'. $theme->theme_id .'\') })', 'onkeyup' => 'Discuss.onKeyUp()', 'style' => 'overflow: hidden; resize: none; height: 42px')) ?>
          <div id="bnt_attaches" class="discuss_attaches clearfix"></div>
          <div class="discuss_buttons clearfix">
            <a class="button left" onclick="Discuss.sendPost('<?php echo $forum->forum_id ?>_<?php echo $theme->theme_id ?>')">Отправить</a>
            <a id="discuss_cancel" class="left button_cancel" onclick="Discuss.cancelPost()" style="display: none">Отмена</a>
            <div id="bnt_progress" class="upload left"><img src="/images/upload.gif" /></div>
            <div class="right">
              <?php echo ActiveHtml::upload('photo', '', 'Прикрепить фотографию', array('onchange' => 'Discuss.attachPhoto(\'#bnt_attaches\', {id})')) ?>
            </div>
          </div>
        </td>
      </tr>
    </table>
  </div>
</div>
<?php endif; ?>