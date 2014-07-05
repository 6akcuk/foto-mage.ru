<?php
$this->pageTitle = Yii::app()->name . ' - Рекламные баннеры';

Yii::app()->getClientScript()->registerCssFile('/css/news.css');
Yii::app()->getClientScript()->registerScriptFile('/js/news_ads.js');

$delta = Yii::app()->getModule('news')->adsPerPage;
?>
<div class="news_search_wrap clear_fix">
  <div rel="filters" class="fl_l">
  </div>
  <div class="fl_r">
    <div class="button_blue">
      <button onclick="NewsAds.add()">Добавить баннер</button>
    </div>
  </div>
</div>
<div class="summary_wrap clear_fix">
  <?php $this->renderPartial('//layouts/pages', array(
    'url' => '/news/ads/index',
    'offset' => $offset,
    'offsets' => $offsets,
    'delta' => $delta,
  )) ?>
  <div class="summary"><?php echo Yii::t('app', '{n} баннер|{n} баннера|{n} баннеров', $offsets) ?></div>
</div>
<div class="ads_news_wrap clear_fix">
<?php if ($ads): ?>
  <?php echo $this->renderPartial('_adslist', array('ads' => $ads, 'offset' => $offset)) ?>
<?php else: ?>
  <div id="no_results">Баннеры еще не добавлены</div>
<?php endif; ?>
</div>
<?php
$this->pageJS = <<<HTML
NewsAds.init();
HTML;
