<?php /** @var $ad AdsNews */ ?>
<?php foreach ($ads as $ad): ?>
  <div id="ads_news<?php echo $ad->ads_id ?>" class="fl_l ads_news">
    <div class="ads_news_header clear_fix">
      <?php echo $ad->city->name ?>, вес: <?php echo $ad->weight ?>
      <span class="fl_r news_signed_by"><?php echo $ad->author->getDisplayName() ?></span>
    </div>
    <div class="ads_news_banner">
      <?php echo ActiveHtml::showUploadImage($ad->banner, 'd') ?>
    </div>
    <div class="ads_news_bottom">
      <a class="ads_news_date"><?php echo ActiveHtml::date($ad->add_date) ?></a> |
      <a onclick="NewsAds.edit(<?php echo $ad->ads_id ?>)">Редактировать</a> |
      <a onclick="NewsAds.delete(<?php echo $ad->ads_id ?>)">Удалить</a>
    </div>
  </div>
<?php endforeach; ?>