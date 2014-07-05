<?php
/** @var DiscussForum $forum
 * @var DiscussForum $subforum
 */
?>
<?php foreach ($forums as $forum): ?>
  <div class="discuss_forum">
    Глобальный форум «<?php echo $forum->title ?>»
    &nbsp;
    <?php echo ActiveHtml::link('Редактировать', '/discuss?act=edit&id='. $forum->forum_id) ?>
    |
    <a onclick="Discuss.deleteForum(<?php echo $forum->forum_id ?>)">Удалить</a>
    <?php foreach ($forum->subforums as $subforum): ?>
    <div class="clearfix">
      <div class="left discuss_forum_icon">
        <?php echo ActiveHtml::showUploadImage($subforum->icon, 'c') ?>
      </div>
      <div class="left discuss_forum_info">
        <?php echo $subforum->title ?>
        <div class="discuss_forum_description">
          <?php echo $subforum->description ?>
        </div>
        <small>
          <?php echo ActiveHtml::link('Редактировать', '/discuss?act=edit&id='. $subforum->forum_id) ?>
          <span class="divide">|</span>
          <a onclick="Discuss.deleteForum(<?php echo $subforum->forum_id ?>)">Удалить</a>
        </small>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endforeach; ?>