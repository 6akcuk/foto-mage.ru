<?php /** @var $post AdvertPost */ ?>
<?php foreach ($posts as $post): ?>
<div id="advert_post<?php echo $post->post_id ?>" class="advert_post clear_fix">
  <div class="fl_l advert_post_avatar">
    <?php echo ActiveHtml::showFirstPhoto($post->photo, 'b') ?>
  </div>
  <div class="fl_l advert_post_info_wrap">
    <div class="advert_post_title">
      <a href="/advert<?php echo $post->post_id ?>" onclick="return nav.go(this, event)"><b><?php echo $post->getTitle() ?></b></a>
    </div>
    <div class="advert_post_price">
      <?php echo ActiveHtml::price($post->price) ?>
    </div>
    <div class="advert_post_data">
      <?php echo $post->category->name ?>
    </div>
    <div class="advert_post_data">
      <?php echo $post->city->name ?>
    </div>
    <div class="advert_post_bottom">
      <a class="advert_post_date" href="/advert<?php echo $post->post_id ?>" onclick="return nav.go(this, event)"><?php echo ActiveHtml::date($post->add_date) ?></a> |
      <a onclick="Advert.edit(<?php echo $post->post_id ?>)">Редактировать</a> |
      <a onclick="Advert.delete(<?php echo $post->post_id ?>)">Удалить</a>
    </div>
  </div>
</div>
<?php endforeach; ?>