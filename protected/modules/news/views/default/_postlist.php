<?php /** @var $post News */ ?>
<?php foreach ($posts as $post): ?>
  <div id="news_post<?php echo $post->news_id ?>" class="news_post clear_fix">
    <div class="fl_l news_post_avatar">
      <?php echo ActiveHtml::showUploadImage($post->facephoto, 'c') ?>
    </div>
    <div class="fl_l news_post_info_wrap">
      <a class="news_post_title" href="/news<?php echo $post->news_id ?>" onclick="return nav.go(this, event)"><b><?php echo $post->title ?></b></a>
      <div class="news_post_text">
        <div class="news_post_fullstory"><?php echo nl2br($post->fullstory) ?></div>
        <div class="news_signed_by"><?php echo $post->author->getDisplayName() ?></div>
      </div>
      <div class="news_post_bottom">
        <a class="news_post_date" href="/advert<?php echo $post->news_id ?>" onclick="return nav.go(this, event)"><?php echo ActiveHtml::date($post->add_date) ?></a> |
        <a onclick="News.edit(<?php echo $post->news_id ?>)">Редактировать</a> |
        <a onclick="News.delete(<?php echo $post->news_id ?>)">Удалить</a>
      </div>
    </div>
  </div>
<?php endforeach; ?>