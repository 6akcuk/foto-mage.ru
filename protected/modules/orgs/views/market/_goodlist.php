<?php /** @var $good MarketGood */ ?>
<?php foreach ($goods as $good): ?>
  <div id="market_good<?php echo $good->good_id ?>" class="market_good clear_fix">
    <div class="fl_l market_good_avatar">
      <?php echo ActiveHtml::showUploadImage($good->facephoto, 'b') ?>
    </div>
    <div class="fl_l market_good_info_wrap">
      <div class="market_good_name">
        <b><?php echo $good->name ?></b>
      </div>
      <div class="market_good_price">
        <?php echo ActiveHtml::price($good->price) ?>
      </div>
      <div class="market_good_data">
        <?php echo $good->category->name ?>
      </div>
      <div class="market_good_story">
        <?php echo nl2br($good->shortstory) ?>
      </div>
      <div class="market_good_bottom">
        <a onclick="Market.editGood(<?php echo $good->good_id ?>)">Редактировать</a> |
        <a onclick="Market.deleteGood(<?php echo $good->good_id ?>)">Удалить</a>
      </div>
    </div>
  </div>
<?php endforeach; ?>